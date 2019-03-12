<?php

namespace EcclesiaCRM\view;

use EcclesiaCRM\MenuBar\MenuBar;
use EcclesiaCRM\MenuBar\Menu;

class MenuRenderer
{
  public static function RenderMenu()
  {
    $menubar = new MenuBar("MainMenuBar");
    $menubar->renderMenu();
  }
}