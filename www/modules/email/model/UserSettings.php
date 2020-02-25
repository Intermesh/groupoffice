<?php
namespace GO\Email\Model;

use go\core\orm\Property;
use function GO;


/**
 * Temporary workaround for saving old settings form a user property;
 */
class UserSettings extends Property {
	public $id;
	public $use_html_markup;
	public $show_cc;
	public $show_bcc;
	public $skip_unknown_recipients;
	public $always_request_notification;
	public $always_respond_to_notifications;
	public $font_size;
	public $sort_email_addresses_by_time;
	public $defaultTemplateId;
	public $sort_on_mail_time;
	
	protected static function defineMapping() {
		return parent::defineMapping()->addTable('core_user');
	}
	
	protected function init() {
		parent::init();
		
		$this->defaultTemplateId = \GO::config()->get_setting("email_defaultTemplateId", $this->id);
		if(!$this->defaultTemplateId) {
			$this->defaultTemplateId = null;
		} else
		{
			$this->defaultTemplateId = (int) $this->defaultTemplateId;
		}
		$this->use_html_markup = !\GO::config()->get_setting("email_use_plain_text_markup", $this->id);
		$this->show_cc = !!\GO::config()->get_setting("email_show_cc", $this->id);
		$this->show_bcc = !!\GO::config()->get_setting("email_show_bcc", $this->id);
		$this->skip_unknown_recipients = !!\GO::config()->get_setting("email_skip_unknown_recipients", $this->id);
		$this->always_request_notification = !!\GO::config()->get_setting("email_always_request_notification", $this->id);
		$this->always_respond_to_notifications = !!\GO::config()->get_setting("email_always_respond_to_notifications", $this->id);
		$this->font_size = \GO::config()->get_setting("email_font_size", $this->id);
		if(!$this->font_size) {
			$this->font_size = "14px";
		}
		$this->sort_email_addresses_by_time = !!\GO::config()->get_setting("email_sort_email_addresses_by_time", $this->id);
		
	}
	
	
	
	protected function internalSave() {
		\GO::config()->save_setting('email_defaultTemplateId', $this->defaultTemplateId, $this->id);
		\GO::config()->save_setting('email_use_plain_text_markup', !empty($this->use_html_markup) ? '0' : '1', $this->id);
		\GO::config()->save_setting('email_show_cc', !empty($this->show_cc) ? 1 : 0, $this->id);
		\GO::config()->save_setting('email_show_bcc', !empty($this->show_bcc) ? 1 : 0, $this->id);
		\GO::config()->save_setting('email_skip_unknown_recipients', !empty($this->skip_unknown_recipients) ? '1' : '0', $this->id);
		\GO::config()->save_setting('email_always_request_notification', !empty($this->always_request_notification) ? '1' : '0', $this->id);
		\GO::config()->save_setting('email_always_respond_to_notifications', !empty($this->always_respond_to_notifications) ? '1' : '0', $this->id);
		\GO::config()->save_setting('email_sort_email_addresses_by_time', !empty($this->sort_email_addresses_by_time) ? '1' : '0', $this->id);
		\GO::config()->save_setting('email_font_size', $this->font_size, $this->id);
		\GO::config()->save_setting('sort_on_mail_time', !empty($this->sort_on_mail_time) ? '1' : '0', $this->id);

		return true;
	}
}
