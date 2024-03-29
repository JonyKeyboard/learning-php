<?php

require_once("globals.php");
require_once("db.php");
require_once("models/User.php");
require_once("models/Message.php");
require_once("dao/UserDAO.php");

$message = new Message($BASE_URL);

$userDao = new UserDAO($conn, $BASE_URL);

// Regate do tipo de formulário
$type = filter_input(INPUT_POST, "type");

// Atualizar usuario
if($type === "update") {

    $userData = $userDao->verifyToken();

    // Receber dados do post
    $name = filter_input(INPUT_POST, "name");
    $lastname = filter_input(INPUT_POST, "lastname");
    $email = filter_input(INPUT_POST, "email");
    $bio = filter_input(INPUT_POST, "bio");

    // Criar novo objeto de usuário
    $user = new User();

    // preencher dados do usuario
    $userData->name = $name;
    $userData->lastname = $lastname;
    $userData->email = $email;
    $userData->bio = $bio;

    // Upload da imagem
    if(isset($_FILES["image"]) && !empty($_FILES["image"]["tmp_name"])){
     
        $image = $_FILES["image"];
        $imageTypes = ["image/jpeg", "image/jpg", "image/png"];
        $jpgArray = ["image/jpeg", "image/jpg"];

        // Checagem de tipo de imagem
        if(in_array($image["type"], $imageTypes)) {

            
            // Checar se jpg
            if(in_array($image["type"], $jpgArray)){

                $imageFile = imagecreatefromjpeg($image["tmp_name"]);

            // image is png
            } else {
                $imageFile = imagecreatefrompng($image["tmp_name"]);
            }

            $imageName = $user->imageGenerateName();

            imagejpeg($imageFile, "./img/users/" . $imageName, 100);

            $userData->image = $imageName;

        } else {
            
            $message->setMessage("Tipo de imagem inválida, insira png ou jpg!", "error", "back");
        
        }

    }

    $userDao->update($userData);

// Atualizar senha do usuário
} else if($type === "changepassword") {

    // Receber dados do post
    $password = filter_input(INPUT_POST, "password");
    $confirmpassword = filter_input(INPUT_POST, "confirmpassword");
    
    $userData = $userDao->verifyToken();
    
    $id = $userData->id;

    //print_r($confirmpassword);
    //print_r($password);exit;

    if($password == $confirmpassword) {

        // Criar novo objeto de usuário
        $user = new User();

        $finalPassword = $user->generatePassword($password);

        $user->password = $finalPassword;
        $user->id = $id;

        $userDao->changePassword($user);

    } else {
        $message->setMessage("Digite senhas iguais!", "error", "back");
    }

} else {
    $message->setMessage("Informações inválidas!", "error", "/index.php");
}