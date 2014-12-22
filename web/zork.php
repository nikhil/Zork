<?
error_reporting(0);
$inputPrompt = $_POST["inputPrompt"];
$start = $_POST["start"];
 $session = $_SERVER['REMOTE_ADDR'];

?>
<html>
<head>
<title>Zork</title>
<link rel=stylesheet href="style.css" type="text/css">
<link rel="stylesheet" href="css/font-awesome.css">
<link rel="stylesheet" href="font-mfizz.css">
<link href='http://fonts.googleapis.com/css?family=Metamorphous' rel='stylesheet' type='text/css'>
</head>
 <body class="main">
<span id="forkongithub"><a href="https://github.com/nikhil/Zork">Zork me on GitHub</a></span>

	<div id="infobar">
		<span class="title">Zork  </span>
		<span class="title-icon">
		Created with: 	
		<i class="icon-c" style="color:red"></i>   +
		<i class="icon-shell" style="color:blue"></i> +
		<i class="icon-php-alt" style="color:purple"></i>  +
		<i class="icon-heroku" style="color:green"></i>
		</span>
	</br>
	</br>
	<div class="commandBox">
	<span class ="commands">Useful Commands:</span>
	<table class="commandtable">
  	<tr>
    	<td><i class="fa fa-arrow-circle-up fa-fw"></i> north</td>
		<td><i class="fa fa-hand-o-up fa-fw"></i> up</td>
		<td><i class="fa fa-power-off fa-fw"></i> quit</td>
		<td><i class="fa fa-comment fa-fw"></i> hello</td>
		<td><i class="fa fa-folder-open fa-fw"></i> open</td>
    
	
  	</tr>
  	<tr>
		<td><i class="fa fa-arrow-circle-down fa-fw"></i> south</td>		
		<td><i class="fa fa-hand-o-down fa-fw"></i> down</td>
		<td><i class="fa fa-tree fa-fw"></i> climb</td>
		<td><i class="fa fa-briefcase fa-fw"></i> inventory </td>
		<td><i class="fa fa-bolt fa-fw"></i> attack</td>

    </tr>
     <tr>
  		<td><i class="fa fa-arrow-circle-right fa-fw"></i> east</td>
		<td><i class="fa fa-eye fa-fw"></i> look</td> 
    	<td><i class="fa fa-sign-in fa-fw"></i> in</td>		
		<td><i class="fa fa-shopping-cart fa-fw"></i> take</td>	
		<td><i class="fa fa-cutlery fa-fw"></i> eat</td>
    </tr>
	<tr>
	<td><i class="fa fa-arrow-circle-left fa-fw"></i> west</td>
	<td><i class="fa fa-medkit"></i> diagnostic</td>
	<td><i class="fa fa-sign-out fa-fw"></i> out</td>
	<td><i class="fa fa-paper-plane fa-fw"></i> throw</td>
	<td><i class="fa fa-beer fa-fw"></i> drink</td>
	</tr>	
</table>
	</div>
	</div>
	<br>
	<div class="console">
	<div id="consoletop">
	<div id="terminal">	
	<div id="box" class="fa-stack fa-lg">
  <i class="fa fa-square fa-stack-2x"></i>
  <i class="fa fa-terminal fa-stack-1x fa-inverse"></i>
   	</div>
	<span id="consoleheader">
	 Zork I: The Great Underground Empire
	</span>

	 
	</div>
				
	</div>
	
<div class="zork">

<?
 $invalid =0;
if($inputPrompt == "quit" or $inputPrompt == "q")
{	$invalid =1;
 if($fileopen = fopen("/tmp/". $session . "_input", "w"))
    {
       fclose($fileopen);

	}
 if($fileopen = fopen("/tmp/". $session . "_output", "w"))
    {
       fclose($fileopen);

	}
 
}	


$apply =0;
$error = 0; 
if($inputPrompt)
{
  $inputPrompt = trim(strtolower($inputPrompt));
  $error = "";

  if(ereg("[^a-z0-9\.\ ]", $inputPrompt))
  {
	$inputPrompt = "invalid";
  }
  if($inputPrompt == PHP_EOL)
  {
	$inputPrompt = "invalid";

  }		  
}
else
{
	$inputPrompt = "invalid";

}

if($invalid !=1)
{
	

	
  if($inputPrompt)
  {
    if($fileopen = fopen("/tmp/". $session . "_input", "a"))
    {
      fwrite ($fileopen, $inputPrompt . "\n");
	  fclose($fileopen);
	}
  }


system("/app/web/spawnzork $session");
$stime = time();
$filename = "/tmp/404/" . $session . "_zork";
while(file_exists($filename))
{
 
}

if($fileopen = fopen("/tmp/" . $session . "_output", "r"))
{
  $outputText = "";
  while(!feof($fileopen))
  {
    $outputText .= fgets($fileopen, 255);
  }
$outputText = str_replace("Welcome to Dungeon", "Welcome to Zork", $outputText);

$outputText = preg_replace("/You are in an open field west of a big white house with a boarded
front door.
There is a small mailbox here./", "", $outputText,1);
$outputText = preg_replace("/Welcome to Zork./", "", $outputText,1);
$outputText = preg_replace("/This version created 11-MAR-91./", "", $outputText,1);
  
}

if($fileopen = fopen("/tmp/" . $session . "_input", "r"))
{
  $inputText = "";
  while(!feof($fileopen))
  {
    $inputText .= fgets($fileopen, 255);
  }
  $inputText = str_replace("GDT\nNC\nND\nNR\nNT\nEX\n", "", $inputText);
    
}

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

print("<br/><form name=\"prompt\" action=\"zork.php\" method=\"post\">");


print("&gt;&nbsp;<input class=\"prompt\" type=\"text\" name=\"inputPrompt\" autocomplete=\"off\">
</form>
");
}
else
{
	$in = "Welcome to Zork (originally Dungeon). This version created 11-MAR-91\n";

	 print("<br>\n$in<br>\n<br>\n");

print("<br/><form name=\"prompt\" action=\"zork.php\" method=\"post\">");


print("&gt;&nbsp;<input class=\"prompt\" type=\"text\" name=\"inputPrompt\" autocomplete=\"off\">
</form>
");



}	
 
?>
</div>
</div>


<script language="javascript">
<!--
  document.prompt.inputPrompt.focus();
 -->
</script>
</body>
</html>
