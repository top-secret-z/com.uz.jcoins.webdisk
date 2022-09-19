<?php
namespace filebase\system\event\listener;
use filebase\data\entry\EntryAction;
use wcf\system\event\listener\IParameterizedEventListener;
use wcf\system\user\jcoins\UserJCoinsStatementHandler;

/**
 * JCoins create file listener.
 *
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.jcoins.webdisk
 */
class JCoinsWebdiskActionListener implements IParameterizedEventListener {
	/**
	 * @inheritdoc
	 */
	public function execute($eventObj, $className, $eventName, array &$parameters) {
		if (!MODULE_JCOINS) return;

		if ($eventObj instanceof EntryAction) {
			switch ($eventObj->getActionName()) {
				case 'triggerPublication':
					foreach ($eventObj->getObjects() as $object) {
						if ($object->userID) {
							UserJCoinsStatementHandler::getInstance()->create('com.uz.jcoins.statement.file', $object->getDecoratedObject());
						}
					}
					break;
					
					// 'enable' calls triggerPublication
					
				case 'disable':
					foreach ($eventObj->getObjects() as $object) {
						if (!$object->isDeleted && $object->userID) {
							UserJCoinsStatementHandler::getInstance()->revoke('com.uz.jcoins.statement.file', $object->getDecoratedObject());
						}
					}
					break;
					
				case 'trash':
					foreach ($eventObj->getObjects() as $object) {
						if (!$object->isDisabled && $object->userID) {
							UserJCoinsStatementHandler::getInstance()->revoke('com.uz.jcoins.statement.file', $object->getDecoratedObject());
						}
					}
					break;
					
				case 'restore':
					foreach ($eventObj->getObjects() as $object) {
						if (!$object->isDisabled && $object->userID) {
							UserJCoinsStatementHandler::getInstance()->create('com.uz.jcoins.statement.file', $object->getDecoratedObject());
						}
					}
					break;
			}
		}
	}
}
