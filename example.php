<?php
require("imapfilter.class.php");

try {
	$filter = new IMAPFilter("your@mailserver.com", "your_password", "mailserver.com", "INBOX");

	// Move every mail from github to a directory (directory and mailbox is the same for IMAP)
	$filter->moveToMailbox("FROM github.com", "INBOX.github");
	// Set every marked spam mail as read
	$filter->markMailboxAsSeen("INBOX.Spam");
	
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}

?>