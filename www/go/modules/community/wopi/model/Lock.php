<?php
namespace go\modules\business\wopi\model;

use go\core\orm\Entity;
use go\core\orm\Mapping;
use go\core\util\DateTime;

class Lock extends Entity {

  const LIFETIME = "PT30M";

  public ?string $id;
  public ?string $serviceId;
  public ?string $fileId;
  public ?\DateTimeInterface $expiresAt = null;

  protected static function defineMapping(): Mapping
  {
    return parent::defineMapping()
      ->addTable('wopi_lock');
  }

  protected function internalValidate()
  {
    if(!isset($this->expiresAt)) {
      $this->expiresAt = new DateTime();
      $this->expiresAt->add(new \DateInterval(self::LIFETIME));
    }

    return parent::internalValidate();
  }

  /**
   * Set the expiry date 30 mins in the future.
   * 
   * @return $this
   */
  public function refresh() {
    $this->expiresAt = new DateTime();
    $this->expiresAt->add(new \DateInterval(self::LIFETIME));
    
    return $this;
  }
}