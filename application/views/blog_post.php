<div class="blogdate"><?php echo substr($post->published_on, 0, -9); ?></div>

<h1><?php echo $post->title; ?></h1>
<br />
<div class="blogbody"><?php echo $post->text; ?></div>

<br />
<div class="blogpostby">Posted by <?php echo $post->author_name; ?></div>
<br /><br />