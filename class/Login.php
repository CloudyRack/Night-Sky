<?php

class Login {

  private $DB;
  private $error;

  public function __construct($DB) {
    $this->DB = $DB;
  }

  public function check_blocked_ip($ip_remote,$table = "login_blacklist",$ammount = 3) {
    if (!$this->isValidIP($ip_remote)) { return false; }

    $time = time();
    $query = "SELECT `id` FROM `".$table."` WHERE (ip_remote = ?) AND timestamp_expires > ? ";

    if ($stmt = $this->DB->GetConnection()->prepare($query)){

      $stmt->bind_param("si",$ip_remote,$time);

      if($stmt->execute()){
          $stmt->store_result();

          $check= "";
          $stmt->bind_result($check);
          $stmt->fetch();

          if ($stmt->num_rows >= $ammount){
            return true;
          } else {
            return false;
          }
      }
    }
  }

  public function addtoBlacklist($ip_remote,$table = "login_blacklist") {
    if (!$this->isValidIP($ip_remote)) { return false; }
    $timestamp = time();
    $expires = strtotime('+30 minutes', $timestamp);
    $stmt = $this->DB->GetConnection()->prepare("INSERT INTO ".$table."(ip_remote,timestamp,timestamp_expires) VALUES (?, ?, ?)");
    $stmt->bind_param('sii', $ip_remote,$timestamp,$expires);
    $rc = $stmt->execute();
    if ( false===$rc ) { $this->error = "MySQL Error"; }
    $stmt->close();
  }

  public function isValidIP($ip) {
    return filter_var($ip,  FILTER_VALIDATE_IP);
  }

}

?>
