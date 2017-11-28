<?php

class IMAPFilter() {
	private $imap_conn;
	
	function __construct($username, $password, $server, $ssl=true, $port=993, $mailbox="INBOX") {
		$this->imap_conn = false;
		
		if($ssl)
			$this->imap_conn = imap_open ("{".$server.":".$port."/imap/ssl}".$mailbox, $username, $password);
		else
			$this->imap_conn = imap_open ("{".$server.":".$port."/imap}".$mailbox, $username, $password);
		
		if(!$this->imap_conn) 
			throw new Exception("IMAP connection failed");
	}
	
	function __destruct() {
		if($this->imap_conn != false)
			imap_close($this->imap_conn);
	}
	
	private function imapSearch() {
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
					$result .= $mail_id.",";
				}
				
				$result = substr($result, 0, strlen($result)-1);
				return $result;
		}
		
		if($result != "")
			return $result;
		else
			return false;
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