<?php
require 'mysql.php';

$target_dir ="Image/";

// $upload_url = 'F:wamp64wwwCVIACAppAPIImage';

$emp_code = $_GET['emp_code'];
$fileinfo = pathinfo($_FILES["fileToUpload"]['name']);

// getting the file extension

$extension = $fileinfo['extension'];
$response = array();

// file path to upload in the server

$file_path = $target_dir . '.' . $extension;
$target_file =$target_dir . basename($_FILES["fileToUpload"]["name"]);

// file url to store in the database

$uploadOk = 1;
$imageFileType = pathinfo($target_file, PATHINFO_EXTENSION);

// Allow certain file formats

if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif")
	{
	
	
 	echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";

	$uploadOk = 0;
	}

// Check if image file is a actual image or fake image

if (isset($_POST["submit"]))
	{
	$check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
	if ($check !== false)
		{
		
		echo "File is an image - " . $check["mime"] . ".";
		
		$uploadOk = 1;
		}
	  else
		{
		
		echo "File is not an image.";
		 
		$uploadOk = 0;
		}
	}

// Check if file already exists

if (file_exists($target_file))
	{
	
	 echo "Sorry, file already exists.";
	 
	$uploadOk = 0;
	}

// Check file size

if ($_FILES["fileToUpload"]["size"] > 500000)
	{
	
	echo "Sorry, your file is too large.";

	$uploadOk = 0;
	}

// Allow certain file formats

if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif")
	{
	
		echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";

	$uploadOk = 0;
	}

// Check if $uploadOk is set to 0 by an error

if ($uploadOk == 0)
	{
	

	echo "Sorry, your file was not uploaded.";

	// if everything is ok, try to upload file

	}
  else
	{
	if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file))
		{

		// $empcode = $_POST['emp_code'];

		updateToDB($emp_code, $target_file);
		
		echo "The file " . basename($_FILES["fileToUpload"]["name"]) . " has been uploaded.";
		
		}
	  else
		{
		
		echo "Sorry, there was an error uploading your file.";
		//echo $response;
		}
	}

function updateToDB($emp_code, $target_file)
	{

	//  $push_id=$data['push_id'];

	$result = array();
	$db = connect_db();
	$Image_url='http://'.$_SERVER['HTTP_HOST'].'/CVIACAppAPI/'.$target_file;
	$sql = "update employee set image_url='$Image_url'" . " where emp_code='$emp_code'";
	$exe = $db->query($sql);
	$last_id = $db->affected_rows;
	$db = null;
	if (!empty($last_id))
		{
		$result['code'] = "0";
		$result['desc'] = "success";
		}
	  else
		{
		$result['code'] = "1003";
		$result['desc'] = "emp_code not found";
		}

	echo json_encode($result);
	}

?>