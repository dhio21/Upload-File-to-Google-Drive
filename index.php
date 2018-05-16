<?php
session_start();
$url_array = explode('?', 'http://'.$_SERVER ['HTTP_HOST'].$_SERVER['REQUEST_URI']);
$url = $url_array[0];

require_once 'google-api-php-client/src/Google_Client.php';
require_once 'google-api-php-client/src/contrib/Google_DriveService.php';
$client = new Google_Client();
$client->setClientId('751562079285-57bmbudao49aa2m4j8dj9jtj55pgck1a.apps.googleusercontent.com');
$client->setClientSecret('PGo9VlYTBVzrNzemnlE8SNq0');
$client->setRedirectUri($url);
$client->setScopes(array('https://www.googleapis.com/auth/drive'));
if (isset($_GET['code'])) {
    $_SESSION['accessToken'] = $client->authenticate($_GET['code']);
    header('location:'.$url);exit;
} elseif (!isset($_SESSION['accessToken'])) {
    $client->authenticate();
}

$files= array();
$dir = dir('files');
while ($file = $dir->read())
    if ($file != '.' && $file != '..') {
        {
            $files[] = $file;
        }
}
$dir->close();

?>

<?php foreach ($files as $file) { ?>
            <!-- <li><?php //echo $file; ?></li> -->
<?php } ?>

<?php

if (!empty($_POST)) {

    $file_tmp = $_FILES['drv_upload']['tmp_name'];
    move_uploaded_file($file_tmp,"files/".$_FILES['drv_upload']['name']);
    $client->setAccessToken($_SESSION['accessToken']);
    $service = new Google_DriveService($client);
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $file = new Google_DriveFile();

    //foreach ($files as $file_name) {

        $file_path = 'files/'.$_FILES['drv_upload']['name'];
        echo $file_path;
        $mime_type = finfo_file($finfo, $_FILES['drv_upload']['name']);
        $file->setTitle($_FILES['drv_upload']['name']);
        $file->setDescription('This is a '.$mime_type.' document');
        $file->setMimeType($mime_type);
        $service->files->insert(
            $file,
            array(
                'data' => file_get_contents($file_path),
                'mimeType' => $mime_type
            )
        );
    //}

    finfo_close($finfo);
    header('location:'.$url);exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Upload Files</title>
</head>
<body>
<form method="post" action="<?php echo $url; ?>" enctype='multipart/form-data'>
            <input type="file" name="drv_upload">
            <input type="submit" value="Upload" name="submit">
        </form>
</body>
</html>