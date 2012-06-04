<?php
    $itemTitle = strip_formatting(item(array('Dublin Core', 'Title')));
    if ($itemTitle != '' && $itemTitle != __('[Untitled]')) {
        $itemTitle = ': &quot;' . $itemTitle . '&quot; ';
    } else {
        $itemTitle = '';
    }
    $itemTitle = __('Edit Item #%s', item('id')) . $itemTitle;
?>
<?php head(array('title'=> $itemTitle, 'bodyclass'=>'items primary','content_class' => 'vertical-nav'));?>
<h1><?php echo $itemTitle; ?></h1>
<?php echo delete_button(null, 'delete-item', __('Delete this Item'), array(), 'delete-record-form'); ?>
<?php include 'form-tabs.php'; // Definitions for all the tabs for the form. ?>

<div id="primary">

    <form method="post" enctype="multipart/form-data" id="item-form" action="">
        <?php include 'form.php'; ?>
        <div>
            <?php echo $this->formSubmit('submit', __('Save Changes'), array('id'=>'save-changes', 'class'=>'submit')); ?>
        </div>
    </form>

</div>

<?php foot();?>
