<?php
$projectTypeID = $_GET['ID'];
if(!$projectTypeID)
{
    echo 'no project ID found';
    die();
}

$projectTypeName = get_the_title($projectTypeID);

echo '<h1>'.$projectTypeName.'</h1>';


$project_custom_meta = get_post_meta($projectTypeID, 'pp_custom_meta', true);


echo '<form action="edit.php?page=ek_project_meta&ID='.$projectTypeID.'&action=update_project_meta_options" method="post">';

$i=1;
while ($i<=5)
{
    echo draw_meta_item($i, $project_custom_meta);
    $i++;


}


echo '<input type="submit" value = "Update" />';

echo '</form>';



function draw_meta_item($this_id, $project_custom_meta)
{


    $html = '';

    $meta_types = array(
        "text",
        "dropdown",
    );


    $meta_name = '';
    $meta_type = '';
    $meta_options = array();
    $show_in_table = '';

    if(isset($project_custom_meta[$this_id]) )
    {
        $meta_name = $project_custom_meta[$this_id]['meta_name'];
        $meta_type = $project_custom_meta[$this_id]['meta_type'];
        $meta_options = $project_custom_meta[$this_id]['meta_options'];
        $show_in_table = $project_custom_meta[$this_id]['meta_show_in_table'];

    }

   // $meta_options = implode("/n", $meta_options);

    $meta_options =  implode( "", $meta_options );


    $html.= '<div class="project_meta_item">';
    $html.= '<h3>Project Meta '.$this_id.'</h3>';
    $html.= '<label for="meta_name_'.$this_id.'">Meta Name</label>';
    $html.= '<input type="text" name="meta_name_'.$this_id.'" id="meta_name_'.$this_id.'" value="'.$meta_name.'" />';
    

    $html.= '<label for="meta_type_'.$this_id.'">Meta Type</label>';
    $html.= '<select name="meta_type_'.$this_id.'">';
    foreach ($meta_types as $type)
    {
        $html.= '<option value="'.$type.'"';
        if($meta_type==$type)
        {
            $html.= ' selected ';
        }

        $html.= '>'.$type.'</option>';
    }

    $html.= '</select>';

    $html.= '<label for="show_in_table_'.$this_id.'">';
    $html.= '<input type="checkbox" name="meta_show_in_table_'.$this_id.'" id="show_in_table_'.$this_id.'"';
    if($show_in_table==true){$html.= ' checked ';}
    $html.= '/> Show in the project table</label>';


    $html.= '<label for="options_'.$this_id.'">Options (dropdown only)</label>';
    $html.= '<textarea name="meta_options_'.$this_id.'" id="options_'.$this_id.'">'.$meta_options.'</textarea>';



    $html.= '</div>';


    return $html;


}
?>
