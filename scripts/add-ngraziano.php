<?php

include_once 'app/Mage.php';

Mage::setIsDeveloperMode(true);

Mage::app();



try {    

    $user = Mage::getModel('admin/user')

        ->setData(array(

            'username'  => 'ngraziano',

            'firstname' => 'Nick',

            'lastname'    => 'Graziano',

            'email'     => 'nick@unleadedgroup.com',

            'password'  => '|3r0n-y-4ur',

            'is_active' => 1

        ))->save();



} catch (Exception $e) {

    echo $e->getMessage();

    exit;

}



try {

    $user->setRoleIds(array(1))

        ->setRoleUserId($user->getUserId())

        ->saveRelations();



    header('Location: ' . Mage::getBaseUrl() . 'admin');



} catch (Exception $e) {

    echo $e->getMessage();

    exit;

}

?>
