<?php
$img = htmlspecialchars($_GET["file"]);
echo "<head>";
echo "<title>Image Viewer</title>";
echo "<link rel=\"stylesheet\" href=\"/src/style.css\">";
echo "<link rel=\"shortcut icon\" href=\"/img/icons/media.png\" type=\"image/x-icon\">";

echo "<meta property=\"og:type\" content=\"website\">";
echo "<meta property=\"og:title\" content=\"Image viewer [" . $img . "]\">";
echo "<meta property=\"og:description\" content=\"A simple image viewer written in 20 minutes using PHP\">";
echo "<meta property=\"og:url\" content=\"http://floppydisk.thisproject.space/\">";
echo "<meta property=\"og:image\" content=\"/img/icons/media.png\">";
echo "</head>";



echo "<h1>" . $img . "</h1>";
echo '<a href="/img/wwc/' . $img . '" download title="Download your favorite WinWorld moments and set them as your wallpaper or something idk">Download image</a>       ';
?>

 | <a href="/wwc.html">Get me outta here!</a>
<hr>
<?php
$image = file_get_contents("/img/wwc/" . $img);
header("Content-type: image/png");
echo '<img src="/img/wwc/' . $img . '">';
?>