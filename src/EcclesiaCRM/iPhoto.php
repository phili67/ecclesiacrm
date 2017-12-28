<?php
namespace EcclesiaCRM;

interface iPhoto
{
    public function getPhoto();
    public function deletePhoto();
    public function setImageFromBase64($base64);
}
