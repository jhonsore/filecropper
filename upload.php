<?php
//  echo json_encode($message);
$file_name = 'file';
$nameFile = $_FILES[$file_name]["name"];
$filepath = $_FILES[$file_name]["tmp_name"];

move_uploaded_file( $_FILES[$file_name]["tmp_name"], $_FILES[$file_name]['name']);

function uploadFileS3 (){

  $file_name = 'file';
  $access_key = __ACCESS_KEY_AMAZON__;
  $secret_key = __SECRET_KEY_AMAZON__;
  $bucket_name = __BUCKET_NAME_AMAZON__;

  $nameFile = $_FILES[$file_name]["name"];
  $filepath = $_FILES[$file_name]["tmp_name"];
  $without_extension = substr($nameFile, 0, strrpos($nameFile, "."));
  $extension = strrchr($nameFile, '.');
  $newNamePrefix = md5(uniqid($without_extension, true));
  $newNamePrefix = $newNamePrefix.$extension;
  $keyname = $newNamePrefix;

  //$extension
  $array_ext = array(
      'gif' => 'image/gif',
      'png' => 'image/png',
      'jpg' => 'image/jpeg'
  );

  $file_ext = "*";

  if(isset($array_ext[$extension]))
  {
      $file_ext = $array_ext[$extension];
  }

  $content_disposition = 'inline; filename='.$nameFile;

  try
  {
      $s3 = new S3Client([
          'region' => 'us-west-2',
          'version' => '2006-03-01',
          'credentials' => [
              'key' => $access_key,
              'secret' => $secret_key,
          ],
      ]);

      // Upload a file.
      $result = $s3->putObject(array(
          'Bucket' => $bucket_name,
          'Key' => $keyname,
          'SourceFile' => $filepath,
          'ContentType' => $file_ext,
          'ContentDisposition' => $content_disposition,
          'ACL' => 'public-read',
          'StorageClass' => 'REDUCED_REDUNDANCY',
          '@http' => [
              'progress' => function($expectedDl, $dl, $expectedUl, $ul)
              {

              }
          ]
      ));

      $file_s3 = $result['ObjectURL'];

      $message = [
          "content" => $content_disposition,
          "file_ext" => $file_ext,
          "name" => $keyname,
          "url" => $file_s3,
          "status" => true,
          "msg" => "Image saved succesfully",
      ];
  }
  catch(\Exception $e)
  {
      $message['msg'] = $e->getMessage();
      $message['status'] = false;
  }

  return $message;
}
