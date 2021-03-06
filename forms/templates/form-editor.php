<?php

use mp_general\base\BaseFunctions;
use mp_general\forms\models\Field;
use mp_general\forms\models\Form;
use mp_general\forms\SSV_Forms;

if (!defined('ABSPATH')) {
    exit;
}

require_once 'customized-form-fields-table.php';

function mp_ssv_show_form_editor(Form $form)
{
    $fields = Field::getAll();
    ?>
    <div class="wrap">
        <h1 class="wp-heading-inline">Add New Form</h1>
        <hr class="wp-header-end">
        <form action="<?= admin_url('admin.php') ?>?page=ssv_forms" method="post">
            <div id="poststuff">
                <div id="post-body" class="columns-2">
                    <div id="post-body-content" style="position: relative;">
                        <div id="titlediv">
                            <div id="titlewrap">
                                <label id="title-prompt-text" for="title">Enter title here</label>
                                <input type="text" name="formTitle" size="30" value="<?= BaseFunctions::escape($form->getTitle(), 'attr') ?>" id="title" spellcheck="true" autocomplete="off" required="required">
                                <input type="hidden" id="formId" name="formId" value="<?= BaseFunctions::escape($form->getId(), 'attr') ?>">
                            </div>
                        </div>
                    </div>
                    <div id="postbox-container-1" class="postbox-container">
                        <div class="meta-box-sortables ui-sortable" style="">
                            <div id="submitdiv" class="postbox ">
                                <button type="button" class="handlediv" aria-expanded="true"><span class="screen-reader-text">Toggle panel: Publish</span><span class="toggle-indicator" aria-hidden="true"></span></button>
                                <h2 class="hndle ui-sortable-handle" style="cursor: pointer;"><span>Publish</span></h2>
                                <div class="inside">
                                    <div class="submitbox" id="submitpost">
                                        <div id="major-publishing-actions" style="background: #ffffff;">
                                            <div id="delete-action">
                                                <a class="submitdelete deletion" href="#">Move to Trash</a>
                                            </div>
                                            <div id="publishing-action">
                                                <?= BaseFunctions::getAdminFormSecurityFields(SSV_Forms::ADMIN_REFERER, false, false); ?>
                                                <input type="submit" name="publish" id="publish" class="button button-primary button-large" value="<?= empty($title) ? 'Publish' : 'Update' ?>">
                                            </div>
                                            <div class="clear"></div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <div class="postbox">
                                <button type="button" class="handlediv" aria-expanded="true"><span class="screen-reader-text">Toggle panel: Customization</span><span class="toggle-indicator" aria-hidden="true"></span></button>
                                <h2 class="hndle ui-sortable-handle" style="cursor: pointer;"><span>Customization</span></h2>
                                <div class="inside">
                                    <p class="post-attributes-label-wrapper"><label class="post-attributes-label" for="submitButtonText">Submit button text</label></p>
                                    <input id="submitButtonText" name="submitButtonText" type="text" value="<?= BaseFunctions::escape($form->getSubmitText(), 'attr') ?>">
                                </div>
                            </div>
                            <div class="postbox">
                                <button type="button" class="handlediv" aria-expanded="true"><span class="screen-reader-text">Toggle panel: Fields</span><span class="toggle-indicator" aria-hidden="true"></span></button>
                                <h2 class="hndle ui-sortable-handle" style="cursor: pointer;"><span>Fields</span></h2>
                                <div class="inside">
                                    <ul id="fieldsList">
                                        <?php foreach ($fields as $field): ?>
                                            <li id="baseField_<?= BaseFunctions::escape($field->getName(), 'attr') ?>" class="baseField" draggable="true" data-field='<?= json_encode($field->getData()) ?>' data-type="Input">
                                                <span><strong><?= BaseFunctions::escape($field->getName(), 'html') ?></strong></span>
                                                <span style="font-size: 9px;">(<?= $field->getType() ?>)</span>
                                                <span style="float: right"><?= BaseFunctions::escape($field->getProperty('type'), 'html') ?></span>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                            <div class="postbox">
                                <button type="button" class="handlediv" aria-expanded="true"><span class="screen-reader-text">Toggle panel: Other</span><span class="toggle-indicator" aria-hidden="true"></span></button>
                                <h2 class="hndle ui-sortable-handle" style="cursor: pointer;"><span>Other</span></h2>
                                <div class="inside">
                                    <ul id="otherList">
                                        <li id="decoration" class="baseField" draggable="true" data-type="Decoration">
                                            <span><strong>Header</strong></span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="postbox-container-2" class="postbox-container">
                        <div class="meta-box-sortables ui-sortable">
                            <div id="postForm" class="postbox">
                                <button type="button" class="handlediv" aria-expanded="true"><span class="screen-reader-text">Toggle panel: Form</span><span class="toggle-indicator" aria-hidden="true"></span></button>
                                <h2 class="hndle ui-sortable-handle" style="cursor: pointer;"><span>Form</span></h2>
                                <div class="inside" style="margin: 0; padding: 0;">
                                    <?php
                                    mp_ssv_show_form_fields_table($form->getFields());
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <br class="clear">
                </div>
            </div>
            <?= BaseFunctions::getAdminFormSecurityFields(SSV_Forms::ADMIN_REFERER, false, false) ?>
        </form>
    </div>
    <?php
}
