<?php
    /*
        This is a simple wordle clone that focuses on retro browsers.
        This includes:
        - Works without JS
        - Works without CSS
        - Works without cookies
        - Can work without color

        Browser support
        ---------------
        Oldest browser tested: Internet Explorer 1.5
        Recommended oldest browser: Internet Explorer 2 (1.5 lacks color support)
        This site will not work in NCSA Mosaic because it lacks support for forms.

        Working website at:
        - https://wordle.ayra.ch/      modern browsers with modern TLS support
        - http://wordle.ayra.ch/       browsers without modern TLS support
        - http://wordle.ayra.ch:8081/  old browsers without HTTP/1.1 support

        How to host this yourself
        -------------------------
        1. Put this file onto your webserver
        2. Create a subdirectory "lists" and make sure it's writable by PHP
        3. Download ALL.TXT and SOLUTION.TXT from https://gitload.net/AyrA/Wordle-C into the lists directory
        4. Open this website, this should create cache files and a key.bin file
        5. You can now make the lists directory readonly
        6. Make the directory inaccessible to web users

        access to lists directory
        -------------------------
        If you do not make the directory inaccessible,
        users can cheat the game. They can download the cached solution file
        and then find the word according to the game id.

        Alternative locations
        ---------------------
        You can change the LIST_DIR constant
        if you want to put the directory somewhere else.
    */

    error_reporting(E_ALL);

    //"lists" directory
    define('LIST_DIR',__DIR__ . '/lists');

    //Word files
    define('LIST_ALL',LIST_DIR . '/ALL.TXT');
    define('LIST_SOLUTION',LIST_DIR . '/SOLUTION.TXT');
    //Cache files
    define('CACHE_ALL',LIST_DIR . '/ALL.cache');
    define('CACHE_SOLUTION',LIST_DIR . '/SOLUTION.cache');
    //Encryption and signing key file
    define('KEYFILE',LIST_DIR . '/key.bin');

    //Lower this for small resolutions to make the cell padding smaller
    define('CELLSIZE',30);
    //Ditto above, but for cell margins
    define('CELLSPACE',5);
    //Number of guesses you have. Increasing makes the game easier
    define('MAX_GUESSES',6);
    //Alphabet (contains all possible characters in your words)
    define('ALPHA','ABCDEFGHIJKLMNOPQRSTUVWXYZ');

    //Numerical constants. Do not change.
    //And if you do for whatever unjustified reason, make sure they're unique.
    define('LETTER_MATCH',2);
    define('LETTER_MISMATCH',1);
    define('LETTER_WRONG',0);

    define('COLOR_MATCH','lime');
    define('COLOR_MISMATCH','yellow');

    //Holds all words
    $all=NULL;
    //Holds possible solutions
    $solutions=NULL;
    //Holds the encryption key
    $enc_key=NULL;

    //Set charset to an old compatible one instead of UTF-8
    header('Content-Type: text/html; charset=iso-8859-1');

    //HTML encode
    function he($x){
        return htmlspecialchars($x,ENT_HTML401|ENT_COMPAT|ENT_SUBSTITUTE);
    }

    //Get value from array without having to test if the key or array exists first.
    function av($a,$k,$d=NULL){
        //Keys in PHP are strings or numbers
        if(!is_string($k) && !is_numeric($k)){
            return $d;
        }
        //Check if array and key exist
        if(!is_array($a) || !isset($a[$k])){
            return $d;
        }
        return $a[$k];
    }

    //Initialize script
    function init(){
        global $all;
        global $solutions;
        global $enc_key;

        //Generate random key if it doesn't exists
        if(!is_file(KEYFILE)){
            file_put_contents(KEYFILE,$enc_key=random_bytes(32));
        }
        else{
            $enc_key=file_get_contents(KEYFILE);
        }

        //Build word cache if necessary
        if(!is_file(CACHE_ALL)){
            $all=array_map('trim',file(LIST_ALL));
            $all=array_unique(array_map('strtoupper',$all));
            file_put_contents(CACHE_ALL,serialize($all));
        }
        else{
            $all=unserialize(file_get_contents(CACHE_ALL));
        }
        //Build solution cache if necessary
        if(!is_file(CACHE_SOLUTION)){
            $solutions=array_map('trim',file(LIST_SOLUTION));
            $solutions=array_unique(array_map('strtoupper',$solutions));
            //Solutions must be shuffled to prevent people from guessing words via game id.
            shuffle($solutions);
            file_put_contents(CACHE_SOLUTION,serialize($solutions));
        }
        else{
            $solutions=unserialize(file_get_contents(CACHE_SOLUTION));
        }
    }

    //Make a guess
    function guess($guess,$real){
        $orig=$guess;
        $ret=array();
        $len=min(strlen($guess),strlen($real));

        $guess=strtoupper($guess);
        $real=strtoupper($real);

        //Do correct matches first
        for($i=0;$i<$len;$i++){
            if($guess[$i]===$real[$i]){
                $ret[$i]=LETTER_MATCH;
                //Blank out letters to avoid false positives later
                $guess[$i]='_';
                $real[$i]='#';
            }
            else{
                //Default to wrong letter to set array to the correct size
                $ret[$i]=LETTER_WRONG;
            }
        }
        //Do incorrect matches next
        for($i=0;$i<$len;$i++){
            if($ret[$i]===LETTER_WRONG){
                $pos=strpos($real,$guess[$i]);
                if($pos!==FALSE){
                    $ret[$i]=LETTER_MISMATCH;
                    //Blank letter to avoid false positives on duplicate letters
                    $real[$pos]='#';
                }
            }
        }
        return array('word'=>$orig,'matrix'=>$ret);
    }

    //Render a table row for a guess
    function tbl($number,$guess, $usecolor=TRUE){
        $w=CELLSIZE;
        $ret= "<tr><td width=$w height=$w bgcolor=red><center><font color=white>" . he($number) . '</font></center></td>';
        $chars=str_split($guess['word']);
        $m=$guess['matrix'];
        foreach($chars as $i=>$c){
            $color='';
            switch($m[$i]){
                case LETTER_MATCH:
                    if($usecolor){
                        $color='bgcolor=' . COLOR_MATCH;
                    }
                    $type='strong';
                    break;
                case LETTER_MISMATCH:
                    if($usecolor){
                        $color='bgcolor=' . COLOR_MISMATCH;
                    }
                    $type='u';
                    break;
                default:
                    $color='';
                    $type='i';
                    break;

            }
            $ret.="<td width=$w height=$w $color><center><$type>" . he($c) . "</$type></center></td>";
        }
        return $ret . '</tr>';
    }

    //Filters the alphabet for used/unused letters
    function filterAlpha($guesses){
        $alpha=str_split(ALPHA);
        foreach($alpha as $char){
            $ret[$char]=FALSE;
            foreach($guesses as $guess){
                if(strpos($guess['word'],$char)!==FALSE){
                    $ret[$char]=TRUE;
                }
            }
        }
        return $ret;
    }

    //Start a new game from the sent game id
    function newGame(){
        if(isValidId()){
            return av($_POST,'id')|0;
        }
        return NULL;
    }

    //Signs data
    function hmac($x){
        global $enc_key;
        return base64_encode(hash_hmac('sha1',$x,$enc_key,TRUE));
    }

    //Decrypts data
    function decrypt($state){
        global $enc_key;
        //0: hmac
        //1: iv
        //2: data
        $parts=explode(':',$state);
        if(count($parts)!==3 || hmac($parts[1] . ':' . $parts[2])!==$parts[0]){
            return NULL;
        }
        $dec=openssl_decrypt(base64_decode($parts[2]),'aes-256-cbc',$enc_key,OPENSSL_RAW_DATA,base64_decode($parts[1]));
        return $dec===FALSE?NULL:json_decode($dec,TRUE);
    }

    //Encrypts data
    function encrypt($state){
        global $enc_key;
        $iv=random_bytes(16);
        $result=openssl_encrypt(json_encode($state),'aes-256-cbc',$enc_key,OPENSSL_RAW_DATA,$iv);
        $sign=base64_encode($iv) . ':' . base64_encode($result);
        //0: hmac
        //1: iv
        //2: data
        return hmac($sign) . ':' . $sign;
    }

    //Gets a game state object for new or existing games
    function getGameState(){
        global $solutions;
        $game=av($_POST,'state');
        if($game!==NULL){
            //Try to decrypt game
            $game=decrypt($game);
        }
        //If game not set (or decryption failed) start a new game
        if($game===NULL){
            $game=array(
                'id'=>newGame(),
                'guesses'=>array(),
                'solved'=>FALSE,
                'hints'=>FALSE,
                'color'=>TRUE
            );
            $game['word']=$solutions[$game['id']-1];
        }
        else{
            //Set defaults to allow upgrade of old game states
            $game['hints']=av($game,'hints')===TRUE;
            $game['color']=av($game,'color')===TRUE;
        }
        return $game;
    }

    //Checks if a word is in the list
    function hasWord($w){
        global $all;
        return in_array(strtoupper($w),$all);
    }

    //Checks if the given word is already guessed
    function isGuessed($w,$g){
        foreach($g as $guess){
            if($guess['word']===$w){
                return TRUE;
            }
        }
        return FALSE;
    }

    //Checks if a game is running
    function isGameRunning(){
        return strtoupper(av($_SERVER,'REQUEST_METHOD','GET'))==='POST';
    }

    //Checks if the submitted game id is valid
    function isValidId(){
        global $solutions;
        $id=av($_POST,'id');
        return is_numeric($id) && ($id|0)>0 && ($id|0)<=count($solutions);
    }

    //Shows an error message
    function showErr($err){
        if($err){
            return '<font color=red><strong>' . he($err) . '</strong></font><br />';
        }
        return '';
    }

    //This calculates all remaining possible solutions for the given array of guesses
    function getPossibleWords($guesses){
        global $solutions;
        $ret=$solutions;
        $charsAny='';
        sort($ret);
        foreach($guesses as $guess){
            $regexMatch='';
            $charsFail='#';
            $matched=array();
            $w=$guess['word'];
            $g=$guess['matrix'];
            //First, retain all definitive clues
            foreach($g as $i=>$num){
                if($num===LETTER_MATCH){
                    $regexMatch.=preg_quote($w[$i],'#');
                    $matched[]=$w[$i];
                }
                else if($num===LETTER_MISMATCH){
                    $regexMatch.='[^' . preg_quote($w[$i],'#') . ']';
                    $matched[]=$w[$i];
                    $charsAny.=$w[$i];
                }
                else{
                    $regexMatch.='.';
                }
            }
            $regexMatch='#^' . $regexMatch . '$#';
            //Build trim mask
            //We do this after matching to avoid problems with duplicate characters in words
            foreach(str_split($w) as $c){
                if(!in_array($c,$matched)){
                    $charsFail.=$c;
                }
            }
            /*You can enable the lines below by adding "/" at the start of this one
            //This allows you to see what exactly is filtered
            //by looking at the page source in your browser.
            echo "<!--
Regex: $regexMatch
Must not exist: $charsFail
Must exist: $charsAny
-->";
            //*/
            $temp=array();
            foreach($ret as $word){
                if(
                    //Discard guesses we've made already
                    $word!==$w &&
                    //Regex must match
                    preg_match($regexMatch,$word) &&
                    //Characters known to not be in the word must not appear anywhere
                    strpbrk($word,$charsFail)===FALSE &&
                    //Chars in wrong positions must appear somewhere in the word
                    //The regex already ensures they're NOT in the yellow position
                    hasAllChars($word,$charsAny)){
                    $temp[]=$word;
                }
            }
            $ret=$temp;
        }
        return $ret;
    }

    //Tests if all given characters appear in the given word
    function hasAllChars($word,$chars){
        if(strlen($chars)===0){
            return TRUE;
        }
        foreach(str_split($chars) as $c){
            if(strpos($word,$c)===FALSE){
                return FALSE;
            }
        }
        return TRUE;
    }

    init();

    $err=NULL;
    $game=NULL;

    //Check if game is running before doing any game related checks
    if(isGameRunning()){
        $mode=av($_POST,'mode');
        //New game
        if($mode==='new'){
            //There is no difference between a random game and a chosen id.
            //The random game form simply has a hidden field with a random id in it
            //This way we can process the forms in the same way
            if(isValidId()){
                $game=getGameState();
            }
            else{
                $err='Invalid game id';
            }
        }
        //User makes a guess or lets the game make a guess
        elseif($mode==='guess'){
            $game=getGameState();
            if(count($game['guesses'])<MAX_GUESSES){
                $guess=av($_POST,'guess');
                if($guess!==NULL){
                    $guess=strtoupper($guess);
                    if(hasWord($guess)){
                        if(!isGuessed($guess,$game['guesses'])){
                            $game['guesses'][]=guess($guess,$game['word']);
                        }
                        else{
                            $err='Word already guessed';
                        }
                    }else{
                        $err='Word not found in list';
                    }
                }
            }
        }
        //User wants to solve
        elseif($mode==='solve'){
            $game=getGameState();
            $game['solved']=TRUE;
            $game['guesses'][]=guess($game['word'],$game['word']);
        }
        //User wants to change hint mode
        elseif($mode==='hint'){
            $game=getGameState();
            $game['hints']=!$game['hints'];
        }
        //User wants to change color mode
        elseif($mode==='color'){
            $game=getGameState();
            $game['color']=!$game['color'];
        }
        //Invalid form action
        else{
            $game=getGameState();
            $err='Invalid game action';
        }

        if($game){
            //Create helper values
            $alpha=filterAlpha($game['guesses']);
            $hasGuess=count($game['guesses'])>0;
        }
        else{
            //Use fake values
            $alpha=filterAlpha(array());
            $hasGuess=FALSE;
        }
    }
    //Self referential URL (without query string)
    $url=av($_SERVER,'PHP_SELF','index.php');
?><!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
<html lang="en">
<head><title>Wordle</title><meta name="viewport" content="width=device-width, initial-scale=1" /></head>
<body vlink=blue link=blue>
<h1>Wordle</h1>

<?php if($game){ ?>
<font face="Courier New,Courier,System,Monospace">
<table border=2>
    <tr>
        <th><h1><center>Guesses</center></h1></th>
        <th><h1><center>Alphabet</center></h1></th>
    </tr>
    <tr>
        <td>
            <?php
                $ended=FALSE;
                if(!$hasGuess){
                    echo 'Waiting for first guess';
                }
                else{
                    echo '<table border=2 cellspacing=' . CELLSPACE . '>';
                    foreach($game['guesses'] as $i=>$g){
                        echo tbl($i+1,$g,$game['color']);
                        $won=$g['word']===$game['word'];
                        if(!$ended && $i+1===MAX_GUESSES || $won){
                            $ended=TRUE;
                            if($game['solved']){
                                $resultcolor=COLOR_MISMATCH;
                                $result='gave up';
                            }
                            else{
                                $resultcolor=$won?COLOR_MATCH:'red';
                                $result=$won?'win':'lose';
                            }
                            echo "<tr><td colspan=6 bgcolor=$resultcolor>Game ended. You $result</td></tr>";
                        }
                    }
                    echo '</table>';
                }
            ?>
        </td>
        <td>
            Used letters are shown in color<br />
            and <u>underlined</u>
            <br />
            <br />
            <center>
            <table border=2 cellspacing=<?=CELLSPACE;?>>
            <?php
                $w=CELLSIZE;
                foreach(str_split(ALPHA,7) as $chunk){
                    echo '<tr>';
                    foreach(str_split($chunk) as $char){
                        $c=$game['color'] && $alpha[$char]?COLOR_MATCH:'white';
                        $type=$alpha[$char]?'u':'span';
                        echo "<td width=$w height=$w bgcolor=$c><center><$type>$char</$type></center></td>";
                    }
                    echo '</tr>';
                }
            ?>
            </table>
            </center>
            &nbsp;<br />
            <?php if($ended){ ?>
                Game ended.<br />
                The word was <strong><?=he($game['word']);?></strong><br />
                <a href="<?=he($url);?>">New Game</a>
            <?php }else{ $remaining=getPossibleWords($game['guesses']); ?>
            <center>
            <form method="post">
                <input type="hidden" name="mode" value="guess" />
                <input type="hidden" name="state" value="<?=encrypt($game);?>" />
                <input type="text" name="guess" placeholder="Guess"
                    required <?php if($hasGuess){ echo 'autofocus'; } ?> />
                <input type="submit" value="Guess" />
            </form><br />
            </center>
            <br />
            <form method="post">
                <input type="hidden" name="mode" value="guess" />
                <input type="hidden" name="state" value="<?=encrypt($game);?>" />
                <input type="hidden" name="guess" value="<?=he($remaining[mt_rand(0,count($remaining)-1)]);?>" />
                <input type="submit" value="Educated guess" />
            </form>
            <br />
            <br />
            <form method="post">
                <input type="hidden" name="mode" value="hint" />
                <input type="hidden" name="state" value="<?=encrypt($game);?>" />
                Show word hints: <strong><?=he($game['hints']?'Yes':'No');?></strong>
                <input type="submit" value="<?=he($game['hints']?'disable':'enable');?>" />
            </form><br />
            <form method="post">
                <input type="hidden" name="mode" value="color" />
                <input type="hidden" name="state" value="<?=encrypt($game);?>" />
                Color: <strong><?=he($game['color']?'Yes':'No');?></strong>
                <input type="submit" value="<?=he($game['color']?'disable':'enable');?>" />
            </form><br />
            <?php } ?>
            <?=showErr($err);?>
            <h3>Stats:</h3>
            Game Id: #<?=he($game['id']);?><br />
            Guesses: <?=he(count($game['guesses']). '/' . MAX_GUESSES);?><br />
            <?php if(!$ended){ ?>
            <form method="post">
                <input type="hidden" name="state" value="<?=encrypt($game);?>" />
                <input type="hidden" name="mode" value="solve" />
                <input type="submit" value="Give up and solve" />
            </form>
            <?php } ?>
        </td>
    </tr>
</table>
<?php if(!$ended){ ?>
    <p><?php
        //This is now done sooner to implement the random guess form
        //$remaining=getPossibleWords($game['guesses']);
        echo 'Number of possible words remaining: ' . count($remaining) . '<br />';
        echo 'Words:<br />';
        if($game['hints']){
            if(count($remaining)>200){
                echo '<i>Word hints enabled. Words will be shown when 200 or less are remaining</i>';
            }
            else{
                echo he(implode(' ',$remaining));
            }
        }
        else{
            echo '<i>Word hints disabled. You can enable them to get a list of all words that are possible according to your guesses.</i>';
        }
    ?></p>
    </font>
    <a href="<?=he($url);?>">New game</a>
<?php } ?>
<?php } else { ?>
<?=showErr($err);?>
<?php
$viewmode=av($_GET,'view');
if($viewmode==='list'){
    $temp=$solutions; //PHP clones arrays instead of referencing them when you assign them somewhere
    sort($temp); //We cloned the array because sort works in-place
    $chunks=array_chunk($temp,8);
    echo '<h2>Word list</h2>';
    echo '<p><a href="' . he($url) . '">Close</a></p>';
    echo '<p>Known words: ' . count($all) .  '<br />Possible solutions: ' . count($solutions) .  '</p>';
    echo '<table border=2 cellpadding=2 cellspacing=2>';
    foreach($chunks as $chunk){
        echo '<tr>';
        foreach($chunk as $word){
            echo '<td>' . he($word) . '</td>';
        }
        echo '</tr>';
    }
    echo '</table>';
    echo '<i>The words do not map directly to game ids</i>';
}elseif($viewmode==='source'){
    echo '<h2>Source code</h2>';
    echo '<p><a href="' . he($url) . '">Close</a></p>';
    highlight_file(__FILE__);
}elseif($viewmode==='help'){ ?>
<p><a href="<?=he($url);?>">Close</a></p>

<h2>About this website</h2>
<p>
    This website is a wordle clone optimized for old machines.
    There is no JS or CSS involved at all.
    It uses only legacy HTML elements
    and loads fairly fast even on slow dialup.
    No cookies are required either.<br />
    If you want an even less advanced version,
    you can check out
    <a href="https://github.com/AyrA/Wordle-C" target="_blank">my version in C</a>
    that's optimized for text terminals and runs under DOS.
</p>
<p>
    The oldest browser this has been tested with is Internet Explorer 1.5.<br />
    Note that this browser is so old it lacks color support.
    If you want color, use at least version 2.0.<br />
    If your browser has broken color support you can disable color during a game.
</p>

<h2>How to play wordle</h2>
<p>
    You have to guess the secret 5 character word within 6 attempts.
    After you make a guess, the background of letters in your word are colored in either
    white, green, or yellow.<br />
    A white letter does not appear anywhere in the word.
    A green letter appears in the correct position.
    A yellow letter is in the word but not the correct position.<br />
    Double letters are handled correctly.
    If the hidden word is <strong>CADET</strong> and you guess
    <strong>TESTS</strong> the first T will be yellow and the second T will be white.
</p>
<p>
    In addition to colors,
    letters are styled to ensure they work on browsers with defective or missing color support.<br />
    <u>underline</u> means the same as green.<br />
    <strong>bold</strong> means the same as yellow.<br />
    <i>italic</i> means the same as white.
</p>
<p>
    No attempt is consumed if you guess a word that is not in the list.
</p>

<h2>Tips</h2>
<p>
    The first 2 or 3 guesses should be used to eliminate as many letters as possible
    unless you have very strong hints for the solution after the first guess already.<br />
    How many words are possible is shown below the game table.
</p>

<h2>Game view explanation</h2>
<p>
    The game field looks like this:
</p>
<table border=2>
    <tr>
        <td rowspan=3>
            This shows the guesses with colored hints
        </td>
        <td>
            This shows the alphabet with used letters in grey
            and <u>underlined</u>
        </td>
    </tr>
    <tr>
        <td>
            In this section you play the game.<br />
            Here you make manual and automatic guesses.<br />
            You can also enable and disable hints here.
        </td>
    </tr>
    <tr>
        <td>
            This shows your game id and the number of guesses.
        </td>
    </tr>
</table>
<p>
    Word hints are disabled by default.
    You can enable and disable them to get a list of possible solutions
    below the game.
    A game always starts with word hints disabled.
</p>

<h2>Educated guessing</h2>
<p>
    The game keeps a list of all words possible according to your guesses.
    The "educated guessing" button
    picks one of the possible words as your next guess.
</p>

<h2>Word hints</h2>
<p>
    The word hint is fairly smart.
    It takes all hints (white, yellow, green) into account.<br />
    It also handles yellow hints correctly
    by filtering words that do contain the letter but not at the given position.<br />
    The only hint so far that it disregards is when you guess a word like
    <strong>TASTY</strong> and the result shows that only one T exists in the solution.
</p>

<?php } else { ?>
<p>
    Welcome to wordle.
    Please pick a game mode below.
</p>
<form method="post">
    <input type="hidden" name="mode" value="new" />
    <input type="hidden" name="id" value="<?=mt_rand(0,count($solutions)-1);?>" /><br />
    <input type="submit" value="Play random game" />
</form><br />
<form method="post">
    Play the given id again:
    <input type="hidden" name="mode" value="new" />
    <input type="text" name="id" size=6 placeholder="Game Id" />
    <input type="submit" value="Play" /><br />
</form><br />
<p><a href="<?=he($url);?>?view=help">Help</a></p>
<p><a href="<?=he($url);?>?view=list">View list of possible solutions</a></p>
<p><a href="<?=he($url);?>?view=source">View source code</a></p>
<?php } ?>
<br />
<br />
<br />
<br />
<br />
<br />
<br />
<br />
<?php } ?>
</body>
</html>
