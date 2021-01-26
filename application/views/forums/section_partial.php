<tr class="forum_tr">
    <td class="forum_icon"><?php            // column 1: icon
        echo (!isset($section->last_read) || max($section->last_modified, $section->last_created) > $section->last_read ? img('images/glyphicons/unread.png') : img('images/glyphicons/glyphicons_036_file.png'));
    ?></td>
    
    <td class="forum_main"><h4><?php        // column 2: Section info
        echo anchor('forums/view_section/'.$section->section_id ,$section->title); ?></h4><h5><?php echo $section->sub_title;
        //if(isset($section->last_read))      // for debugging
        //    echo " ; <b><font color='white'>LAST READ: ".$section->last_read."</font></b>";
    ?></h5></td>
    
    <td class="forum_info"><?php            // column 3: Post & Topic counts
        echo $section->num_posts; ?> Posts <br /> <?php echo $section->num_topics; 
    ?> Topics </td>
    
    <td class="forum_info"><?php            // column 4: Last Post date
        echo max($section->last_created, $section->last_modified)
    ?></td>
</tr>