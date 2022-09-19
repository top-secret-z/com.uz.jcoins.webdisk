<?php
namespace filebase\system\event\listener;
use filebase\data\entry\Entry;
use wcf\system\event\listener\IParameterizedEventListener;
use wcf\system\exception\NamedUserException;
use wcf\system\user\jcoins\UserJCoinsStatementHandler;
use wcf\system\WCF;

/**
 * JCoins download file listener.
 *
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.jcoins.webdisk
 */
class JCoinsWebdiskDownloadListener implements IParameterizedEventListener {
	/**
	 * @inheritdoc
	 */
	public function execute($eventObj, $className, $eventName, array &$parameters) {
		if (!MODULE_JCOINS) return;
		
		// only users and not file owner
		if (!WCF::getUser()->userID) return;
		if ($eventObj->entry->userID == WCF::getUser()->userID) return;
		
		// check available JCoins
		if ($eventName == 'readData' && !JCOINS_ALLOW_NEGATIVE) {
			$statement = UserJCoinsStatementHandler::getInstance()->getStatementProcessorInstance('com.uz.jcoins.statement.download.user');
			if ($statement->calculateAmount() < 0 && ($statement->calculateAmount() * -1) > WCF::getUser()->jCoinsAmount) {
				throw new NamedUserException(WCF::getLanguage()->getDynamicVariable('wcf.jcoins.amount.tooLow'));
			}
		}
		
		// check for password and activation
		$entry = $eventObj->entry->getDecoratedObject();
		if (!empty($entry->password)) {
			$sql = "SELECT	COUNT (*) as count
					FROM 	filebase".WCF_N."_entry_activated
					WHERE 	userID = ? AND entryID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([WCF::getUser()->userID, $entry->entryID]);
			$count = $statement->fetchColumn();
			if (!$count) return;
		}
		
		// assign JCoins for download
		UserJCoinsStatementHandler::getInstance()->create('com.uz.jcoins.statement.download', $entry);
		UserJCoinsStatementHandler::getInstance()->create('com.uz.jcoins.statement.download.user', $entry,['userID' => WCF::getUser()->userID]);
	}
}
