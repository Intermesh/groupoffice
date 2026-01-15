<?php
namespace go\modules\business\wopi\model;
use go\core\orm\Entity;
use go\core\orm\Mapping;
use go\core\util\DateTime;
use GO\Files\Model\File;

class Token extends Entity {

  protected ?int $id;
  public ?string $serviceId;
  public ?string $userId;
  public ?string $fileId;
  protected string $token;

  protected ?DateTime $expiresAt = null;

  const LIFETIME = 'P1D';

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
   * Get a token
   * 
   * @param int $serviceId
   * @param int $userId
   * 
   * @return static
   */
  public static function get($serviceId, $userId) {    
    $token = self::find()
      ->where([
        'serviceId' => $serviceId, 
        'userId' => go()->getUserId()])
      ->andWhere('expiresAt', '>', new \DateTime())
      ->single();

    if(!$token) {
      $token = new self();
      $token->serviceId = $serviceId;
      $token->userId = $userId;
      
      if(!$token->save()) {
        throw new \Exception("Failed to create WOPI token");
      }
    }

    return $token;
  }
  
  

}