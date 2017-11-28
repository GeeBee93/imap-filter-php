<?php

class IMAPFilter {
	private $imap_conn;
	private $default_mailbox;
	private $default_mailbox_server;
	
	function __construct($username, $password, $server, $mailbox="INBOX", $ssl=true, $port=993) {
		$this->imap_conn = false;
		$this->default_mailbox = $mailbox;
		
		if($ssl)
			$this->default_mailbox_server = "{".$server.":".$port."/imap/ssl}";
		else
			$this->default_mailbox_server = "{".$server.":".$port."/imap}";
		
		$this->imap_conn = imap_open ($this->default_mailbox_server.$this->default_mailbox, $username, $password);
		
		if(!$this->imap_conn) 
			throw new Exception("IMAP connection failed");
	}
	
	function __destruct() {
		$this->closeConnection();
	}
	
	public function closeConnection() {
		if($this->imap_conn != false)
			imap_close($this->imap_conn);
		
		$this->imap_conn = false;
	}
	
	private function imapSearch($filter) {
		$result = imap_search($this->imap_conn, $filter);
		
		if(!$result)
			return false;
		
		if(count($result) > 1) 
			return $result;
		
		if(count($result) == 1) {
			$result = $result[0];
			return $result;
		}
		
		return false;
	}
		
	public function searchInMailbox($filter) {
		$result = $this->imapSearch($filter);
		$return = "";
		
		if(!$result)
			return false;
		
		if(is_array($result)) {
				foreach($result as $mail_id) {
					$return .= $mail_id.",";
				}
				
				$return = substr($return, 0, strlen($return)-1);
				return $return;
		}
		
		if($result != "")
			return $result;
		else
			return false;
	}
	
	public function markMessagesAsSeen($filter) {
		$result = $this->searchInMailbox($filter);
		
		if(!$result)
			return false;	
		
		return imap_setflag_full($this->imap_conn, $result, "\\Seen");
	}
	
	public function changeMailbox($mailbox) {
		if($this->imap_conn != false) {
			return imap_reopen($this->imap_conn, $this->default_mailbox_server.$mailbox);
		}
	}
	
	public function markMailboxAsSeen($mailbox) {
		if($this->changeMailbox($mailbox)) {
			$this->markMessagesAsSeen("ALL");
			
			return $this->changeMailbox($this->default_mailbox);
		}
	}

	
	public function moveToMailbox($filter, $dest_mailbox) {
		$result = $this->searchInMailbox($filter);
		
		if(!$result)
			return false;

		if(!imap_mail_move($this->imap_conn, $result, $dest_mailbox))
			return false;
		
		return imap_expunge($this->imap_conn);
	}	
}
?>