<?php
/**
 *  @var string $title
 *  @var array  $user
 */
?>

<div class="container-fluid">
    <ul class="nav nav-tabs" id="myTab" role="tablist">
      <li class="nav-item" role="presentation">
        <a class="nav-link active" id="tab1-tab" data-bs-toggle="tab" href="#tab1" role="tab">Смотреть</a>
      </li>
      <li class="nav-item" role="presentation">
        <a class="nav-link" id="tab2-tab" data-bs-toggle="tab" href="#tab2" role="tab">Редактировать</a>
      </li>
    </ul>
    <div class="tab-content" id="myTabContent">
      <div class="tab-pane fade show active" id="tab1" role="tabpanel">
        <?php include DIR_INC . '/user_view.php'; ?>
      </div>
      <div class="tab-pane fade" id="tab2" role="tabpanel">
        <?php include DIR_INC . '/user_form.php'; ?>
      </div>
    </div>
</div>



