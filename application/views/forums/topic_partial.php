<tr>
    
    <?php // Column 1: ICON
    ?>
    <td class="forum_icon"><?php
    if(!isset($topic->last_read)  || $topic->last_post_created_on > $topic->last_read)
        echo img('images/glyphicons/unread.png');
    else
        echo img('images/glyphicons/glyphicons_036_file.png');
    ?></td>
    
    <?php // Column 2: Topic link
          //           by ___
    ?>
    <td class="forum_main"><h3><?php echo anchor('forums/view_topic/'.$topic->topic_id, $topic->title); ?></h3>
    <?php
    if ($posts_per_page > 0)
        $numpages = ceil(( $topic->num_replies ) / $posts_per_page);
    else
        $numpages = 1;
    if( $numpages > 1)
    {
        echo ' [ ';
        for ($i = 1; $i <= $numpages; $i++)
        {
            if ($i == 1)
                echo anchor('forums/view_topic/'.$topic->topic_id, '1');
            else
                echo anchor('forums/view_topic/'.$topic->topic_id.'/'.(($i-1) * $posts_per_page), ', '.$i);
        }
        echo ' ] ';
    }
    echo "by ".$topic->topic_by_username;
    echo " on ".$topic->created_on;
    //if(isset($topic->last_read))      // for debugging
    //    echo " ; <b><font color='white'>LAST READ: ".$topic->last_read.'</font></b>';
    ?> </td>
    
    <?php // Column 3: Replies
          //           Views
    ?>
    <td class="forum_info_stats"><?php echo $topic->num_replies-1; ?> Replies <br /> <?php echo $topic->num_views; ?> Views </td>
    
    <?php // Column 4: Last Post Date
          //           by ___
    ?>
    <td class="forum_info_lastdate">
        <?php echo $topic->last_post_created_on; ?>
        <br />
        <?php echo 'by '.$topic->last_post_by_username; ?>
    </td>
    
    
    
</tr>