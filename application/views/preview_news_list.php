<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <link href="<?php echo base_url();?>bootstrap/css/bootstrap.min.css" rel="stylesheet"></link>
    <title>Главная страница</title>
</head>
<body>
<div class="container-fluid" style="margin-top: 20px;">
<?php foreach($articles as $item): ?>
 <div class="col-lg-10">
  <div class="panel panel-info ">
    <div class="panel-heading">
        <h3 class="panel-title"><span class="glyphicon glyphicon-paperclip"></span> <strong><?=$item["pubDate"];?> </strong>  <strong class="text-danger "> <?=$item["title"];?> </strong><span class="badge" style="float:right;"><?=$item["count_views"];?></span></h3>
    </div>
    <div class="panel-body">
        <div class="media">
          <div class="media-left">
            <a href="#">
              <img class="media-object" src="<?=$item["enclosure"];?>" style = " width: 200px;height: 140px;"alt="...">
            </a>
          </div>
          <div class="media-body">
            <?=html_entity_decode($item["description"]);?>
          </div>
            <div id="full_content_link" style="float: right">
                <a href="<?php echo base_url();?>index.php/pages/article/<?=$item["id"];?>" class="btn btn-link">Подробнее</a>
            </div>
        </div>
    </div>
  </div>
 </div>
 <?php endforeach;?>
</div>
<nav style="text-align:center;">
    <ul class="pagination">
        <?php echo $this->pagination->create_links();?>
    </ul>
</nav>
</body>
<script src="<?php echo base_url();?>bootstrap/js/bootstrap.min.js"></script>
</html>