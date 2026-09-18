<?php

namespace go\modules\community\marketplaceserver\lib;

/**
 * Minimal DB-backed rate limiter for the public registration endpoint. Records
 * every attempt in `marketplaceserver_reg_attempt` and caps attempts per IP and per e-mail
 * within a sliding window. DB-backed (not APCu) so it works headless and across
 * workers. All values are bound as parameters.
 */
class RateLimiter
{
    /**
     * How many times the per-IP limit an e-mail address may be tried from all
     * addresses together. A per-e-mail cap equal to the per-IP one would let
     * anyone lock a customer out by sending a few bad logins for their address;
     * this one only bounds a guessing attack spread over many addresses.
     */
    const EMAIL_FACTOR = 10;

    /**
     * The address an attempt is counted under. An IPv6 client usually controls a
     * whole /64, so counting single IPv6 addresses would not limit anything.
     *
     * @param string $ip
     * @return string
     */
    public static function ipBucket(string $ip): string
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $bin = inet_pton($ip);
            if ($bin !== false) {
                return inet_ntop(substr($bin, 0, 8) . str_repeat("\0", 8)) . '/64';
            }
        }
        return $ip;
    }

    /**
     * Record this attempt and report whether it is still within the limit: at
     * most $maxPerWindow per address, and EMAIL_FACTOR times that for one e-mail
     * from all addresses together.
     *
     * @param string $ip
     * @param string|null $email
     * @param int $maxPerWindow max attempts allowed per identifier in the window
     * @param int $windowMinutes sliding window size
     * @return bool true = allowed; false = throttled (respond 429)
     * @throws \Exception
     */
    public static function hit(string $ip, ?string $email, int $maxPerWindow = 5, int $windowMinutes = 60): bool
    {
        $pdo = go()->getDbConnection()->getPDO();

        $ip = substr(self::ipBucket($ip), 0, 45);
        $email = $email !== null && $email !== '' ? substr($email, 0, 190) : null;

        $ins = $pdo->prepare('INSERT INTO `marketplaceserver_reg_attempt` (`ip`, `email`, `createdAt`) VALUES (?, ?, NOW())');
        $ins->execute([$ip, $email]);

        $since = (new \DateTime())->sub(new \DateInterval('PT' . $windowMinutes . 'M'))->format('Y-m-d H:i:s');

        $byIpStmt = $pdo->prepare('SELECT COUNT(*) FROM `marketplaceserver_reg_attempt` WHERE `ip` = ? AND `createdAt` >= ?');
        $byIpStmt->execute([$ip, $since]);
        $byIp = (int) $byIpStmt->fetchColumn();

        if ($byIp > $maxPerWindow) {
            return false;
        }
        if ($email === null) {
            return true;
        }

        $byEmailStmt = $pdo->prepare('SELECT COUNT(*) FROM `marketplaceserver_reg_attempt` WHERE `email` = ? AND `createdAt` >= ?');
        $byEmailStmt->execute([$email, $since]);
        return (int) $byEmailStmt->fetchColumn() <= $maxPerWindow * self::EMAIL_FACTOR;
    }

    /**
     * Record a hit against an arbitrary bucket key (not an IP/e-mail pair) and
     * report whether it is still within the limit. Used to throttle authenticated
     * endpoints per customer (e.g. license re-issuance), reusing the same ledger
     * table + pruning as the public auth limiter.
     *
     * @param string $key opaque bucket identifier, stored in the `ip` column
     * @param int $maxPerWindow max hits allowed per key in the window
     * @param int $windowMinutes sliding window size
     * @return bool true = allowed; false = throttled
     * @throws \Exception
     */
    public static function hitKey(string $key, int $maxPerWindow, int $windowMinutes = 60): bool
    {
        $pdo = go()->getDbConnection()->getPDO();
        $key = substr($key, 0, 45);

        $ins = $pdo->prepare('INSERT INTO `marketplaceserver_reg_attempt` (`ip`, `email`, `createdAt`) VALUES (?, NULL, NOW())');
        $ins->execute([$key]);

        $since = (new \DateTime())->sub(new \DateInterval('PT' . $windowMinutes . 'M'))->format('Y-m-d H:i:s');
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM `marketplaceserver_reg_attempt` WHERE `ip` = ? AND `createdAt` >= ?');
        $stmt->execute([$key, $since]);

        return ((int) $stmt->fetchColumn()) <= $maxPerWindow;
    }

    /**
     * Delete attempt rows older than the given age (housekeeping; call from cron
     * or opportunistically). Returns rows removed.
     *
     * @param int $olderThanHours
     * @return int
     * @throws \Exception
     */
    public static function prune(int $olderThanHours = 24): int
    {
        $pdo = go()->getDbConnection()->getPDO();
        $cutoff = (new \DateTime())->sub(new \DateInterval('PT' . $olderThanHours . 'H'))->format('Y-m-d H:i:s');
        $stmt = $pdo->prepare('DELETE FROM `marketplaceserver_reg_attempt` WHERE `createdAt` < ?');
        $stmt->execute([$cutoff]);
        return $stmt->rowCount();
    }
}
