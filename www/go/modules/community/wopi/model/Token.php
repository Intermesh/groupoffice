<?php
namespace go\modules\community\wopi\model;
use go\core\orm\Entity;
use go\core\orm\Mapping;
use go\core\util\DateTime;

class Token extends Entity {

  protected ?int $id;
  public ?string $serviceId;
  public ?string $userId;
  public ?int $fileId;
  protected string $token;

  protected ?DateTime $expiresAt = null;

  const LIFETIME = 'P1D';

  /**
   * A token is only reused when it's valid for at least this period.
   */
  const REUSE_MIN_REMAINING = 'PT12H';

  protected static function defineMapping(): Mapping
  {
    return parent::defineMapping()->addTable('wopi_token');
  }

  public function getToken() {
    return $this->token;
  }

  /**
   * @return DateTime
   */
  public function getExpiresAt() {
    return $this->expiresAt;
  }

  protected function internalValidate()
  {
    if(!isset($this->token)) {
      $this->token = bin2hex(random_bytes(16));
      $this->expiresAt = new DateTime();
      $this->expiresAt->add(new \DateInterval(self::LIFETIME));
    }

    return parent::internalValidate();
  }

  public function isExpired() {
    return (new \DateTime()) > $this->expiresAt;
  }

  /**
   * Get a token for one file. The WOPI client may only access this file with it.
   *
   * An existing token is reused when it has at least half of its lifetime left, so that it doesn't expire during an
   * editing session.
   *
   * @param int $serviceId
   * @param int $userId
   * @param int $fileId
   * @return static
   * @throws \Exception
   */
  public static function get(int $serviceId, int $userId, int $fileId): static {
    $minExpiry = new DateTime();
    $minExpiry->add(new \DateInterval(self::REUSE_MIN_REMAINING));

    $token = self::find()
      ->where([
        'serviceId' => $serviceId,
        'userId' => $userId,
        'fileId' => $fileId])
      ->andWhere('expiresAt', '>', $minExpiry)
      ->single();

    if(!$token) {
      $token = new self();
      $token->serviceId = $serviceId;
      $token->userId = $userId;
      $token->fileId = $fileId;

      if(!$token->save()) {
        throw new \Exception("Failed to create WOPI token");
      }
    }

    return $token;
  }

  /**
   * Find the token for a WOPI request on a file.
   *
   * Returns null when the token doesn't exist, has expired, belongs to another file or its user is disabled.
   *
   * @param string $token The access_token sent by the WOPI client
   * @param int $fileId The file ID of the WOPI request
   * @return static|null
   */
  public static function findValid(string $token, int $fileId): ?static {
    if($token === "") {
      return null;
    }

    $model = self::find()->where(['token' => $token])->single();
    if(!$model || $model->isExpired() || (int) $model->fileId !== $fileId) {
      return null;
    }

    $enabled = go()->getDbConnection()
      ->selectSingleValue('enabled')
      ->from('core_user')
      ->where('id', '=', $model->userId)
      ->single();

    return $enabled ? $model : null;
  }

  /**
   * Delete all WOPI tokens of a user
   *
   * @param int $userId
   * @return bool
   */
  public static function deleteForUser(int $userId): bool {
    return go()->getDbConnection()->delete('wopi_token', ['userId' => $userId])->execute();
  }

  /**
   * Delete expired tokens
   *
   * @return bool
   */
  public static function collectGarbage(): bool {
    return go()->getDbConnection()->delete('wopi_token', (new \go\core\orm\Query())->where('expiresAt', '<', new DateTime()))->execute();
  }
}
