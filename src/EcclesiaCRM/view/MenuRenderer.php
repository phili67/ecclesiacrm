<?php

namespace EcclesiaCRM\view;

use EcclesiaCRM\MenuBar\MenuBar;

class MenuRenderer
{
  public static function RenderMenu(): string
  {
    $menubar = new MenuBar("MainMenuBar");
    return $menubar->renderMenu();
  }
}