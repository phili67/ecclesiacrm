<?php
/*******************************************************************************
 *
 *  filename    : Include/Footer-Short.php
 *  last change : 2002-04-22
 *  description : footer that appear on the bottom of all pages
 *
 *  http://www.ecclesiacrm.com/
 *  Copyright 2001-2002 Phillip Hullquist, Deane Barker
  *
 ******************************************************************************/
 
        use EcclesiaCRM\dto\SystemURLs;

        ?>
					</td>
				</tr>
			</table>

		</td>
	</tr>
</table>

</body>

<script src="<?= SystemURLs::getRootPath() ?>/skin/js/ShowAge.js"></script>

</html>
<?php

// Turn OFF output buffering
ob_end_flush();
?>
