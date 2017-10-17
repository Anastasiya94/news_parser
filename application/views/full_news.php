<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE HTML>
<html>
    <head>
        <meta charset="utf-8">
        <link href="<?php echo base_url();?>bootstrap/css/bootstrap.min.css" rel="stylesheet"></link>
        <title>Новость</title>
    </head>
    <body>
      <div class="container" style="margin-top: 20px;">
          <span class="badge" style="float:left;height:17.25px;margin-top: 2px;"><?=$count_views?></span> -- <span class="label label-info"><?=$pubDate?> </span>
          <header><h2><?=$title?></h2></header>
          <em><?=html_entity_decode($description)?></em>
          <div>
           <img class="media-object" src="<?=$enclosure?>" align="left" vspace="5" hspace="5">
            <p><?= html_entity_decode($full_content)?></p>
          </div>
      </div>

    </body>

</html>