<?php

namespace EcclesiaCRM\Tasks;


interface iTask
{

  public function isActive();
  public function isAdmin();
  public function getLink();
  public function getTitle();
  public function getDesc();

}
