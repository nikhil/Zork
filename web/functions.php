<?

function connectdb()
{
  global $mysql_link, $dbname, $dbmachine, $dbuser, $dbpasswd;

  $mysql_link = mysql_connect($dbmachine, $dbuser, $dbpasswd);
  if(!$mysql_link)
  {
    // database error display sql configuration setup
    print("<br>Error: unable to connect to database server.<br><br>");
    displaysqladmin("admin");
    exit;
  }
  mysql_select_db($dbname, $mysql_link);
  if(mysql_errno())
  {
    // database error display sql configuration setup
    print("<br>Error " . mysql_errno() . ": database not found.<br><br>");
    displaysqladmin("admin");
    exit;
  }
}

function usersonline()
{        
  global $mysql_link;

  $query = "SELECT UserID FROM session WHERE "        
    . "LastAction > '" . date("Y-m-d H:i:s", time()-600) . "'";

  $mysql_result = mysql_query($query, $mysql_link);
  while($row = mysql_fetch_row($mysql_result))
  {
    if($useridlast == $row[0] AND $useridlast)
    {
      --$online;            
    }                
    $useridlast = $row[0];
    ++$online;
    if($row[0] == "")
    {
      ++$anonymous;
    }
  }

  if($online == 1)
  {
    $result = "There is $online user playing Zork.";
  }
  elseif($online > 1)
  {
    $result = "There are $online users playing Zork.";
  }

  //  if($anonymous)
  //  {
  //    if($anonymous == 1)
  //    { 
  //      $result .= "<br>of those $anonymous is anonymous";
  //    } 
  //    else
  //    {
  //      $result .= "<br>of those $anonymous are anonymous";
  //    }
  //  }

  return($result);
}


function startsession()
{
  global $mysql_link, $dbname, $session;
  global $user_ID;

  // fetch/set session ID
  $Query = "SELECT ID FROM session WHERE LastAction < '";
  $Query .= date("Y-m-d H:i:s", (time()-1800));
  $Query .= "'";
  $mysql_result = mysql_query($Query, $mysql_link);

  while($row = mysql_fetch_row($mysql_result))
  {
    unlink("/tmp/404/" . $row[0] . "_input");    
    unlink("/tmp/404/" . $row[0] . "_output");    
  }

  $Query = "DELETE FROM session WHERE LastAction < '";
  $Query .= date("Y-m-d H:i:s", (time()-1800));
  $Query .= "'";
  mysql_query($Query, $mysql_link);

  //assign the variable session the variable variable $dbname

  if(isset($session))
  {
    $Query = "SELECT ID, LastAction, LastLocatn, UserID ";
    $Query .= "FROM session ";
    $Query .= "WHERE ID='$session' ";
    $mysql_result = mysql_query($Query, $mysql_link);

    if(mysql_numrows($mysql_result))
    {
      $row = mysql_fetch_row($mysql_result);
      $session_ID = $row[0];
      $session_LastAction = $row[1];
      $session_LastLocatn = $row[2];
      $session_UserID = $row[3];
      $user_ID = $row[3];

      $Query = "UPDATE session ";
      $Query .= "SET LastAction = now(), ";
      $Query .= "    LastLocatn = \"$session_LastLocatn\" ";
      $Query .= "WHERE ID='$session' ";
      mysql_query($Query, $mysql_link);

    }
    else
    {
      // invalid session, create a new one
      $session = SessionID(8);
      $session_LastAction = date("Y-m-d H:i:s");

      setcookie("$dbname",$session);
      system(" touch /tmp/404/" . $session . "_input");
      system(" echo GDT >> /tmp/404/" . $session . "_input");
      system(" echo NC >> /tmp/404/" . $session . "_input");
      system(" echo ND >> /tmp/404/" . $session . "_input");
      system(" echo NR >> /tmp/404/" . $session . "_input");
      system(" echo NT >> /tmp/404/" . $session . "_input");
      system(" echo EX >> /tmp/404/" . $session . "_input");

      $Query = "INSERT INTO session (ID, LastAction, UserID) ";
      $Query .= "VALUES ('$session', '$session_LastAction', '') ";
      mysql_query($Query, $mysql_link);
    }
  }

  //no session exists, create it
  if($session == "")
  {
    $session = SessionID(8);
    $session_LastAction = date("Y-m-d H:i:s");

    setcookie("$dbname",$session);
    system(" touch /tmp/404/" . $session . "_input");
    system(" echo GDT >> /tmp/404/" . $session . "_input");
    system(" echo NC >> /tmp/404/" . $session . "_input");
    system(" echo ND >> /tmp/404/" . $session . "_input");
    system(" echo NR >> /tmp/404/" . $session . "_input");
    system(" echo NT >> /tmp/404/" . $session . "_input");
    system(" echo EX >> /tmp/404/" . $session . "_input");
 
    $Query = "INSERT INTO session (ID, LastAction, UserID)";
    $Query .= "VALUES ('$session', '$session_LastAction', '') ";
    mysql_query($Query, $mysql_link);
  }
}

function killsession()
{
  global $mysql_link, $dbname, $session;

  // fetch/set session ID
  $Query = "DELETE FROM session WHERE ID = '$session' ";
  mysql_query($Query, $mysql_link);
}


function SessionID($length)
{
  global $mysql_link;

  $Pool = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
  $Pool .= "abcdefghijklmnopqrstuvwxyz";
  $Pool .= "0123456789";

  //  better random session id generator
  //  added by volt, 20 april
  //  you need to seed your random generator.

  mt_srand( (double) microtime() * 1000000 );
  //  $sid = mt_rand( 1000000, 99999999 );

  for($index = 0; $index < $length; $index++)
  {
    $sid = substr($Pool,
                   (mt_rand()%(strlen($Pool))), 1);
  }

  $query = "SELECT ID FROM session WHERE ID = '$sid'";
  $mysql_result = mysql_query($query, $mysql_link);

  if(mysql_numrows($mysql_result))
  {
    // session exists.. grab a new one
    $sid = SessionID(8);
  }
  return($sid);
}

function login($inputName, $inputPassword)
{
  global $user_ID, $user_Name, $user_password, $user_email;
  global $user_personalinfo1, $user_personalinfo2, $user_personalinfo3;
  global $user_admin, $user_lastread, $user_messages, $user_logins;
  global $user_quotingmode, $user_previewmode, $user_threadingmode;
  global $user_minmessages, $user_mindays, $user_maxdays, $user_signature;

  global $mysql_link, $apply, $session, $funct, $curlog;

  $err = 0;
  
  // Query database for user
  $Query = "SELECT ID, Name, password, email, admin, logins "
    . "FROM users "
    . "WHERE Name='$inputName' ";
  $mysql_result = mysql_query($Query, $mysql_link);

  if(mysql_numrows($mysql_result))
  {
    // Get ID Info
    $row = mysql_fetch_row($mysql_result);
    $item_ID = $row[0];
    $item_UserID = $row[0];
    $item_Name = $row[1];
    $item_password = $row[2];
    $item_email = $row[3];
    $item_admin = $row[4];
    $item_logins = $row[5];
  }
  else
  {
    if($apply == 1)
    {
      $apply = 0;

      // Check if already a user.. if so pissoff
      $Query = "SELECT * ";         
      $Query .= "FROM users ";
      $Query .= "WHERE Name='$inputName' ";
      $mysql_result = mysql_query($Query, $mysql_link);

      if(mysql_numrows($mysql_result))
      {
        // user exists we can't add them again
        return "<br>Sorry, that user already exists.";
      }
      elseif($inputPassword == "")
      {
        // passwords don't match
        return "<br>Sorry, you must supply a password.";
      }
      else
      {
        // If indeed new user add to users table
        $row = mysql_fetch_row($mysql_result);
        $Query = "INSERT INTO users (Name, password, email, admin, logins ) ";
        $Query .= "VALUES ('$inputName', '";
        $Query .= crypt($inputPassword, $inputName);
        $Query .= "', '', 0, 1 )";
        mysql_query($Query, $mysql_link);
        $Query = "SELECT ID, Name, password, email, personalinfo1, personalinfo2, personalinfo3, admin, lastread, "
        . "messages, logins, quotingmode, previewmode, threadingmode, minmessages, mindays, maxdays, signature "
        . "FROM users "
        . "WHERE Name='$inputName' ";
        $mysql_result = mysql_query($Query, $mysql_link);

        if(mysql_numrows($mysql_result))
        {
          // Get ID Info
          $row = mysql_fetch_row($mysql_result);
          $user_ID = $row[0];
          $user_Name = $row[1];
          $user_password = $row[2];
          $user_email = $row[3];
          $user_admin = $row[4];
          $user_logins = $row[5];
  
          // give the first user admin rights
          if($user_ID == 1)
          {
            $Query = "UPDATE users SET admin = '255' where ID = 1";
            mysql_query($Query, $mysql_link);
          }
        }
        else
        {
          // error adding user
          return "<br>There was a problem encountered adding the user.";
        }

        // update session with UserID
        $Query = "UPDATE session ";
        $Query .= "SET UserID = '$user_ID' ";
        $Query .= "WHERE ID='$session' ";
        mysql_query($Query, $mysql_link);
        $session_UserID = $user_ID;
  
        return "<br>Welcome $user_Name.";
      }
    }
    else
    {
      // user doesn't exist
      $apply = 1;
      return "<br>That user was not found.<br><br>Do you wish to apply as new with the information provided?";
    }
  }

  if(crypt($inputPassword, $item_Name) == $item_password)
  {
    // update session with UserID
    $Query = "UPDATE session ";
    $Query .= "SET UserID = '$item_ID' ";
    $Query .= "WHERE ID='$session' ";
    mysql_query($Query, $mysql_link);

    $session_UserID = $item_ID;
    $session_LastRead = $item_lastread;

    // increment number of logins
    $Query = "UPDATE users SET logins = '";
    $Query .= $item_logins + 1;
    $Query .= "' WHERE ID = '$item_ID' ";
    $mysql_result = mysql_query($Query, $mysql_link);

    $Query = "SELECT ID, Name, password, email, admin, logins "
    . "FROM users "
    . "WHERE ID='$session_UserID' ";
    $mysql_result = mysql_query($Query, $mysql_link);

    if(mysql_numrows($mysql_result))
    {
      // Get ID Info
      $row = mysql_fetch_row($mysql_result);
      $user_ID = $row[0];
      $user_Name = $row[1];
      $user_password = $row[2];
      $user_email = $row[3];
      $user_admin = $row[4];
      $user_logins = $row[5];
    }

    return "<br>Welcome $user_Name.";
  }
  else
  {
    // bad password
    return "<br>Sorry, that isn't the correct password.";
  }
}


?>
