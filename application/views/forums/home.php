<h1>Succession Wars Forums</h1>
<br />
<table class="forum_table">
     <tr class="forum_tr">
        <td></td>
        <td>Forum Section</td>
        <td></td>
        <td>Last Post Date</td>
    </tr>
    
<?php foreach($sections as $section) 
{
    $data['section'] = $section;
    $this->load->view('forums/section_partial', $data);
}
?>
</table>