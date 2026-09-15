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
     * Record this attempt and report whether it is still within the limit.
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

        $ip = substr($ip, 0, 45);
        $email = $email !== null && $email !== '' ? substr($email, 0, 190) : null;

        $ins = $pdo->prepare('INSERT INTO `marketplaceserver_reg_attempt` (`ip`, `email`, `createdAt`) VALUES (?, ?, NOW())');
        $ins->execute([$ip, $email]);

        $since = (new \DateTime())->sub(new \DateInterval('PT' . $windowMinutes . 'M'))->format('Y-m-d H:i:s');

        $byIpStmt = $pdo->prepare('SELECT COUNT(*) FROM `marketplaceserver_reg_attempt` WHERE `ip` = ? AND `createdAt` >= ?');
        $byIpStmt->execute([$ip, $since]);
        $byIp = (int) $byIpStmt->fetchColumn();

        $byEmail = 0;
        if ($email !== null) {
            $byEmailStmt = $pdo->prepare('SELECT COUNT(*) FROM `marketplaceserver_reg_attempt` WHERE `email` = ? AND `createdAt` >= ?');
            $byEmailStmt->execute([$email, $since]);
            $byEmail = (int) $byEmailStmt->fetchColumn();
        }

        return $byIp <= $maxPerWindow && $byEmail <= $maxPerWindow;
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
