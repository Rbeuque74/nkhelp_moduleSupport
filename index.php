<?php 
// -------------------------------------------------------------------------//
// Nuked-KlaN - PHP Portal                                                  //
// http://www.nuked-klan.org                                                //
// -------------------------------------------------------------------------//
// This program is free software. you can redistribute it and/or modify     //
// it under the terms of the GNU General Public License as published by     //
// the Free Software Foundation; either version 2 of the License.           //
// -------------------------------------------------------------------------//
/*Module Support / nkhelp */
if (!defined("INDEX_CHECK"))
{
    die ("<div style=\"text-align: center;\">You cannot open this page directly</div>");
} 

global $nuked, $language, $user;
translate("modules/Support/lang/" . $language . ".lang.php");

opentable();

if (!$user)
{
    $lvlUser = 0; 
} 
else
{
    $lvlUser = $user[1]; 
} 

$ModName = basename(dirname(__FILE__));
$level_access = nivo_mod($ModName);
if ($lvlUser >= $level_access && $level_access > -1)
{
    function index()
    {
        global $lvlUser, $nuked, $user;
        if($lvlUser)
        {
            $tickets = recupTickets(); 
        }
        ?>
<div style="text-align:center;">
    <h2><?php echo _SUPPORT; ?></h2>
</div>
<table width="100%" border="1" cellspacing="1" cellpadding="2">
    <tbody>
        <tr>
            <td><b>ID</b></td>
            <td><b>Sujet</b></td>
            <td><b>Cat&eacute;gorie</b></td>
            <td><b>Date</b></td>
            <td><b>Op&eacute;rations</b></td>
        </tr>
        <?php if($lvlUser == 0){ ?>
        <tr>
            <td colspan="5">Vous n'&ecirc;tes pas enregistr&eacute;, impossible de suivre des sujets</td>
        </tr>
            <?php }
        else {     
            while($t = mysql_fetch_assoc($tickets)){ ?>
        <tr>
            <td><?php echo $t["id"]; ?></td>
            <td><?php echo $t["titre"]; ?></td>
            <td><?php $cat = getCatName($t["id"]); foreach($cat as $c){ $CAT = $c["nom"];} echo $CAT; ?></td>
            <td><?php echo strftime("%x %H:%M", $t["date"]); ?></td>
            <td><a href="index.php?file=Support&amp;op=view&amp;id=<?php echo $t["id"]; ?>">viewreply</a> close</td>
        </tr>
        <?php  
            }
        }
?>
        <tr></tr>
    </tbody>
</table>


<?php
        
	
	echo "<br /><form method=\"post\" action=\"index.php?file=Contact&amp;op=sendmail\" onsubmit=\"return verifchamps()\">\n"
	. "<table style=\"margin-left: auto;margin-right: auto;text-align: left;\" cellspacing=\"1\" cellpadding=\"3\" border=\"0\">\n"
	. "<tr><td align=\"center\"><big><b>" . _CONTACT . "</b></big><br /><br />" . _CONTACTFORM . "</td></tr>\n"
	. "<tr><td>&nbsp;</td></tr><tr><td><b>" . _YNICK . " : </b>&nbsp;<input id=\"ns_pseudo\" type=\"text\" name=\"nom\" size=\"26\" value=\"" . $user[2]. "\" /></td></tr>\n"
	. "<tr><td><b>" . _YMAIL . " : </b>&nbsp;<input id=\"ns_email\" type=\"text\" name=\"mail\" value=\"\" size=\"30\" /></td></tr>\n"
	. "<tr><td><b>" . _YSUBJECT . " : </b>&nbsp;<input id=\"ns_sujet\" type=\"text\" name=\"sujet\" value=\"\" size=\"36\" /></td></tr>\n"
	. "<tr><td>&nbsp;</td></tr><tr><td><b>" . _YCOMMENT . " : </b><br /><textarea class=\"editorsimpla\" id=\"ns_corps\" name=\"corps\" cols=\"60\" rows=\"12\"></textarea></td></tr>\n"
	. "<tr><td align=\"center\"><br /><input type=\"submit\" class=\"bouton\" value=\"" . _SEND . "\" /></td></tr></table></form><br />\n";
    }
    
    function viewThread($thread_ID)
    {
        global $lvlUser, $nuked, $user;
        $thread = recupThread($thread_ID);
        if($lvlUser == 0 || $thread["auteur_id"] != $user[0])
        {
            echo _PASPROPRIOTICKET;
        }
        else
        {
            $messages = recupThreadMessages($thread_ID);
        }
        ?>
<div style="text-align:center;">
    <h2><?php echo _SUPPORT; ?></h2>
</div>
<?php    
            while($m = mysql_fetch_assoc($messages)){ if($m["admin"] == 0){ ?>
<div style="border:1px solid black; width:100%;"><?php echo _YOUWROTE . strftime("%x %H:%M", $m["date"]) ?></div>
<div style="border:1px solid black; width:100%;"><?php echo $m["texte"] ?></div>
<?php } else { ?>
<div style="border:1px solid black; width:100%; background-color: yellow;"><?php echo $m["auteur"] . _WROTE . strftime("%x %H:%M", $m["date"]) ?></div>
<div style="border:1px solid black; width:100%; background-color: yellow;"><?php echo $m["texte"] ?></div>

        <?php  
            }}
?>



<?php
        
	
	echo "<br /><form method=\"post\" action=\"index.php?file=Contact&amp;op=sendmail\" onsubmit=\"return verifchamps()\">\n"
	. "<table style=\"margin-left: auto;margin-right: auto;text-align: left;\" cellspacing=\"1\" cellpadding=\"3\" border=\"0\">\n"
	. "<tr><td align=\"center\"><big><b>" . _CONTACT . "</b></big><br /><br />" . _CONTACTFORM . "</td></tr>\n"
	. "<tr><td>&nbsp;</td></tr><tr><td><b>" . _YNICK . " : </b>&nbsp;<input id=\"ns_pseudo\" type=\"text\" name=\"nom\" size=\"26\" value=\"" . $user[2]. "\" /></td></tr>\n"
	. "<tr><td><b>" . _YMAIL . " : </b>&nbsp;<input id=\"ns_email\" type=\"text\" name=\"mail\" value=\"\" size=\"30\" /></td></tr>\n"
	. "<tr><td><b>" . _YSUBJECT . " : </b>&nbsp;<input id=\"ns_sujet\" type=\"text\" name=\"sujet\" value=\"\" size=\"36\" /></td></tr>\n"
	. "<tr><td>&nbsp;</td></tr><tr><td><b>" . _YCOMMENT . " : </b><br /><textarea class=\"editorsimpla\" id=\"ns_corps\" name=\"corps\" cols=\"60\" rows=\"12\"></textarea></td></tr>\n"
	. "<tr><td align=\"center\"><br /><input type=\"submit\" class=\"bouton\" value=\"" . _SEND . "\" /></td></tr></table></form><br />\n";
    }



    function recupTickets()
    {
	global $nuked, $user;
    	$sql = mysql_query("SELECT * FROM ". $nuked["prefix"] ."_support_threads WHERE auteur_id = '" . $user[0] . "' AND closed = 0 ORDER BY id DESC");
        return $sql;
    }
    function recupThreadMessages($thread_id)
    {
	global $nuked, $user;
    	$sql = mysql_query("SELECT * FROM ". $nuked["prefix"] ."_support_messages WHERE thread_id = '" . $thread_id . "' ORDER BY date ASC");
        return $sql;
    }
    function recupThread($thread_id)
    {
	global $nuked, $user;
    	$sql = mysql_query("SELECT * FROM ". $nuked["prefix"] ."_support_threads WHERE id = '" . $thread_id . "' ORDER BY id DESC LIMIT 0,1");
        $sql = mysql_fetch_assoc($sql);
        return $sql;
    }
    function getCatName($catID)
    {
	global $nuked;

    	$sql = mysql_query("SELECT nom FROM ". $nuked["prefix"] ."_support_cat WHERE id = '" . $catID . "' ORDER BY id DESC LIMIT 0,1");
        return $sql;
    }
    function sendmail($nom, $mail, $sujet, $corps)
    {
	global $nuked, $user_ip, $nuked;

    	$time = time();
    	$date = strftime("%x %H:%M", $time);
    	$contact_flood = $nuked['contact_flood'] * 60;

    	$sql = mysql_query("SELECT date FROM " . CONTACT_TABLE . " WHERE ip = '" . $user_ip . "' ORDER BY date DESC LIMIT 0, 1");
    	$count = mysql_num_rows($sql);
    	list($flood_date) = mysql_fetch_array($sql);
    	$anti_flood = $flood_date + $contact_flood;

    	if ($count > 0 && $time < $anti_flood)
    	{
	    echo "<br /><br /><div style=\"text-align: center;\">" . _FLOODCMAIL . "</big></div><br /><br />";
	    redirect("index.php", 3);
    	}
    	else
    	{
	    $nom = trim($nom);
	    $mail = trim($mail);
	    $sujet = trim($sujet);

	    $subjet = $sujet . ", " . $date;
	    $corp = $corps . "\r\n\r\n\r\n" . $nuked['name'] . " - " . $nuked['slogan'];
	    $from = "From: " . $nom . " <" . $mail . ">\r\nReply-To: " . $mail . "\r\n";
	    $from.= "Content-Type: text/html\r\n\r\n";

	    if ($nuked['contact_mail'] != "") $email = $nuked['contact_mail'];
	    else $email = $nuked['mail'];	
		$corp = secu_html(html_entity_decode($corp));
		
	    mail($email, $subjet, $corp, $from);

	    $name = htmlentities($nom, ENT_QUOTES);
	    $email = htmlentities($mail, ENT_QUOTES);
	    $subject = htmlentities($sujet, ENT_QUOTES);
	    $text = secu_html(html_entity_decode($corps, ENT_QUOTES));

	    $add = mysql_query("INSERT INTO " . CONTACT_TABLE . " ( `id` , `titre` , `message` , `email` , `nom` , `ip` , `date` ) VALUES ( '' , '" . $subject . "' , '" . $text . "' , '" . $email . "' , '" . $name . "' , '" . $user_ip . "' , '" . $time . "' )");
		$upd = mysql_query("INSERT INTO ". $nuked['prefix'] ."_notification  (`date` , `type` , `texte`)  VALUES ('".$time."', '1', '"._NOTCON.": [<a href=\"index.php?file=Contact&page=admin\">lien</a>].')");
	    echo "<br /><br /><div style=\"text-align: center;\">" . _SENDCMAIL . "</div><br /><br />";
	    redirect("index.php", 3);
    	}
    }

    switch($_REQUEST['op']){

	case"sendmail":
	sendmail($_REQUEST['nom'], $_REQUEST['mail'], $_REQUEST['sujet'], $_REQUEST['corps']);
	break;

	case"index":
	index();
	break;
    
        case"view":
            viewThread($_REQUEST["id"]);
            break;

	default:
	index();
	break;
    }

} 
else if ($level_access == -1)
{
    echo "<br /><br /><div style=\"text-align: center;\">" . _MODULEOFF . "<br /><br /><a href=\"javascript:history.back()\"><b>" . _BACK . "</b></a><br /><br /></div>";
} 
else if ($level_access == 1 && $visiteur == 0)
{
    echo "<br /><br /><div style=\"text-align: center;\">" . _USERENTRANCE . "<br /><br /><b><a href=\"index.php?file=User&amp;op=login_screen\">" . _LOGINUSER . "</a> | <a href=\"index.php?file=User&amp;op=reg_screen\">" . _REGISTERUSER . "</a></b><br /><br /></div>";
} 
else
{
    echo "<br /><br /><div style=\"text-align: center;\">" . _NOENTRANCE . "<br /><br /><a href=\"javascript:history.back()\"><b>" . _BACK . "</b></a><br /><br /></div>";
} 

closetable();

?>