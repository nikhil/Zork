<?
  require("config.php");
  $session = $dbname;
  $inputPrompt = $_POST["inputPrompt"];
  require("functions.php");
  connectdb();
  startsession();

  header("Expires: Mon, 21 Sep 2000 07:30:00 GMT");
  header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
  header("Cache-Control: no-cache, must-revalidate");
  header("Pragma: no-cache" );
?>

<HTML>
<HEAD>
<TITLE>... 404 Error: Now what??? ...</TITLE>
<LINK REL=stylesheet HREF="style.css" TYPE="text/css">
</HEAD>

<BODY BGCOLOR="#000000" TEXT="#CCCCCC" LINK="#FFFFFF" ALINK="#FFFFFF" VLINK="#CCCCCC">
<BR><BR><BR><BR>
<p class="zork">

<?
$apply =0;
if($inputPrompt)
{
  $inputPrompt = trim(strtolower($inputPrompt));
  $error = "";

  if(ereg("[^a-z0-9\.\ ]", $inputPrompt))
  {
    $error = "Invalid input.";
    $inputPrompt = "";
  }

  if($inputPrompt == "q" or $inputPrompt == "quit"
     or substr($inputPrompt,0,2) == "q."
     or substr($inputPrompt,0,5) == "quit."
     or strstr($inputPrompt, "q.")
     or strstr($inputPrompt, "quit"))
  {
    $error = "<br>You can't quit now.";
    $inputPrompt = "score\n";
  }

  if(substr($inputPrompt,0,5) == "login")
  {
    $command = explode(" ", $inputPrompt);
    $inputName = $command[1];
    $inputPassword = $command[2];

    $error = login($command[1],$command[2]);
    $inputPrompt = "";
  }
  
  if(($inputPrompt == "yes" or $inputPrompt == "y") and $apply == 1 and $inputSession == $session)
  {
    $command = explode(" ", $inputPrompt);
    $error = login($inputName,$inputPassword);
    $inputPrompt = "";
  }
  
  if($inputPrompt == "active")
  {
    $error = "<br>" . usersonline();
    $inputPrompt = "";
  }

  if(strstr($inputPrompt, "save") and $user_ID == 0)
  {
    $error = "<br>You must login first to save.";
    $inputPrompt = "";
  }
  elseif(strstr($inputPrompt, "save"))
  {
    system("cp /tmp/404/" . $session . "_input /tmp/404/save/save_$user_ID");
    $error = "<br>Game saved.";
    $inputPrompt = "";
  }

  if(strstr($inputPrompt, "restore") and $user_ID == 0)
  {
    $error = "<br>You must login first to restore.";
    $inputPrompt = "";
  }
  elseif(strstr($inputPrompt, "restore"))
  {
    system("cp /tmp/404/save/save_$user_ID /tmp/404/" . $session . "_input");
    $error = "<br>Game restored.";
    $inputPrompt = "";
  }

  if(strstr($inputPrompt, "gdt"))
  {
    $error = "<br>You can't do that.";
    $inputPrompt = "";
  }

  if($inputPrompt == "help")
  {
    $error = "<br>&gt; Help<br>
<br>
Useful commands:<br>
<br>
The 'BRIEF' command suppresses printing of long room descriptions<br>
for rooms which have been visited. The 'SUPERBRIEF' command suppresses<br>
printing of long room descriptions for all rooms. The 'VERBOSE'<br>
command restores long descriptions.<br>
The 'INFO' command prints information which might give some idea<br>
of what the game is about.<br>
The 'LOGIN <username> <password>' command allows you to log in, or<br>
or create a new account if you have never logged in before.<br>
The 'ACTIVE' shows the number of active users playing Zork.<br>
The 'SAVE' command saves the state of the game for later continuation.<br>
you must LOGIN before you are allowed to save.<br>
The 'RESTORE' command restores a saved game.<br>
you must LOGIN before you are allowed to restore.<br>
The 'INVENTORY' command lists the objects in your possession.<br>
The 'LOOK' command prints a description of your surroundings.<br>
The 'SCORE' command prints your current score and ranking.<br>
The 'TIME' command tells you how long you have been playing.<br>
The 'DIAGNOSE' command reports on your injuries, if any.<br>
<br>
Command abbreviations:<br>
<br>
The 'INVENTORY' command may be abbreviated 'I'.<br>
The 'LOOK' command may be abbreviated 'L'.<br>
<br>
Containment:<br>
<br>
Some objects can contain other objects. Many such containers can<br>
be opened and closed. The rest are always open. They may or may<br>
not be transparent. For you to access (e.g., take) an object<br>
which is in a container, the container must be open. For you<br>
to see such an object, the container must be either open or<br>
transparent. Containers have a capacity, and objects have sizes;<br>
the number of objects which will fit therefore depends on their<br>
sizes. You may put any object you have access to (it need not be<br>
in your hands) into any other object. At some point, the program<br>
will attempt to pick it up if you don't already have it, which<br>
process may fail if you're carrying too much. Although containers<br>
can contain other containers, the program doesn't access more than<br>
one level down.<br>
<br>
Fighting:<br>
<br>
Occupants of the dungeon will, as a rule, fight back when<br>
attacked. In some cases, they may attack even if unprovoked.<br>
Useful verbs here are 'ATTACK &lt;villain&gt; WITH &lt;weapon&gt;', 'KILL',<br>
etc. Knife-throwing may or may not be useful. You have a<br>
fighting strength which varies with time. Being in a fight,<br>
getting killed, and being injured all lower this strength.<br>
Strength is regained with time. Thus, it is not a good idea to<br>
fight someone immediately after being killed. Other details<br>
should become apparent after a few melees or deaths.<br>
<br>
Command parser:<br>
<br>
A command is one line of text terminated by a carriage return.<br>
For reasons of simplicity, all words are distinguished by their<br>
first six letters. All others are ignored. For example, typing<br>
'DISASSEMBLE THE ENCYCLOPEDIA' is not only meaningless, it also<br>
creates excess effort for your fingers. Note that this trunca-<br>
tion may produce ambiguities in the intepretation of longer words.<br>
<br>
You are dealing with a fairly stupid parser, which understands<br>
the following types of things--<br>
<br>
Actions:<br>
Among the more obvious of these, such as TAKE, PUT, DROP, etc.<br>
Fairly general forms of these may be used, such as PICK UP,<br>
PUT DOWN, etc.<br>
<br>
Directions:<br>
NORTH, SOUTH, UP, DOWN, etc. and their various abbreviations.<br>
Other more obscure directions (LAND, CROSS) are appropriate in<br>
only certain situations.<br>
<br>
Objects:<br>
Most objects have names and can be referenced by them.<br>
<br>
Adjectives:<br>
Some adjectives are understood and required when there are<br>
two objects which can be referenced with the same 'name' (e.g.,<br>
DOORs, BUTTONs).<br>
<br>
Prepositions:<br>
It may be necessary in some cases to include prepositions, but<br>
the parser attempts to handle cases which aren't ambiguous<br>
without. Thus 'GIVE CAR TO DEMON' will work, as will 'GIVE DEMON<br>
CAR'. 'GIVE CAR DEMON' probably won't do anything interesting.<br>
When a preposition is used, it should be appropriate; 'GIVE CAR<br>
WITH DEMON' won't parse.<br>
<br>
Sentences:<br>
The parser understands a reasonable number of syntactic construc-<br>
tions. In particular, multiple commands (separated by commas)<br>
can be placed on the same line.<br>
<br>
Ambiguity:<br>
The parser tries to be clever about what to do in the case of<br>
actions which require objects that are not explicitly specified.<br>
If there is only one possible object, the parser will assume<br>
that it should be used. Otherwise, the parser will ask.<br>
Most questions asked by the parser can be answered.<br>
<br>
Remember, you must login with 'LOGIN <username> <password>' before<br>
you can save or restore your games.";

    $inputPrompt = "";

  }

  // add input to text stream
  if($inputPrompt)
  {
    if($fileopen = fopen("/tmp/404/". $session . "_input", "a"))
    {
      fwrite ($fileopen, $inputPrompt . "\n");
      fclose($fileopen);
    }
  }
}

system("/opt/lampp/htdocs/zork/spawnzork $session &> /dev/null");
$stime = time();
$filename = "/tmp/404/" . $session . "_zork";
while(file_exists($filename))
{
  usleep(10);
  clearstatcache();  
  // wait for process to end
  if($stime + 15 < time())
  {
    system("killall -9 /tmp/404/" . $session . "_zork");
    print("there was an error.");
    killsession();        
    exit;
  }
}

if($fileopen = fopen("/tmp/404/" . $session . "_output", "r"))
{
  $outputText = "";
  while(!feof($fileopen))
  {
    $outputText .= fgets($fileopen, 255);
  }
/* GDT>
>GDT>No cyclops.
GDT>No deaths.
GDT>No robber.
GDT>No troll.
GDT>
*/
  $outputText = str_replace(">GDT>", "", $outputText);
  $outputText = str_replace("GDT>", "", $outputText);
  $outputText = str_replace("No cyclops.\n", "", $outputText);
  $outputText = str_replace("No deaths.\n", "", $outputText);
  $outputText = str_replace("No robber.\n", "", $outputText);
  $outputText = str_replace("No troll.\n", "", $outputText);
// <villain> WITH <weapon>
  $outputText = str_replace("<villain> WITH <weapon>", "&lt;villain&gt; WITH &lt;weapon&gt;", $outputText);
//  $outputText = str_replace(">", "<br>", $outputText);
  $outputText = str_replace("Welcome to Dungeon", "Welcome to Zork", $outputText);
  $outputText = str_replace("This version created 11-MAR-91.", "This version created 11-MAR-91 (PHP mod 25-OCT-2001)", $outputText);
    
//  print(nl2br($inputText));
}

if($fileopen = fopen("/tmp/404/" . $session . "_input", "r"))
{
  $inputText = "";
  while(!feof($fileopen))
  {
    $inputText .= fgets($fileopen, 255);
  }
/* GDT>
>GDT>No cyclops.
GDT>No deaths.
GDT>No robber.
GDT>No troll.
GDT>
*/
  $inputText = str_replace("GDT\nNC\nND\nNR\nNT\nEX\n", "", $inputText);
    
//  print(nl2br($inputText));
}

// explode (string separator, string string [, int limit])

$output = explode(">", $outputText);
$input = explode("\n", $inputText);

$output = array_slice($output, -11, 11);
$input = array_slice($input, -10, 10);


$nomore = 1;
while($nomore)
{
  $out = array_shift($output);
  $in = array_shift($input);
  if(!$out and !$in)
  {
    $nomore = 0;
  }
  if($out)
  {
    print(nl2br($out));
  }
  if($in)
  {
    print("<br>\n&gt; $in<br>\n<br>\n");
  }
}




if($error)
{
  print("$error<br><br>");
}

print("<form name=\"prompt\" action=\"index.php\" method=\"post\">");
if($apply == 1)
{
  print("<input type=\"hidden\" name=\"apply\" value=\"$apply\">\n");
  print("<input type=\"hidden\" name=\"inputName\" value=\"$inputName\">\n");
  print("<input type=\"hidden\" name=\"inputPassword\" value=\"$inputPassword\">\n");
  print("<input type=\"hidden\" name=\"inputSession\" value=\"$session\">\n");
}
print("&gt;&nbsp;<input class=\"prompt\" type=\"text\" name=\"inputPrompt\" size=\"64\">
</form>
");

?>

<BR>
<BR>
</p>

<script language="javascript">
<!--
  document.prompt.inputPrompt.focus();
 -->
</script>
</body>
</html>
