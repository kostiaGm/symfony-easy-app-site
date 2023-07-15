/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
//import './styles/app.css';

import './styles/global.scss'

const $ = require('jquery');
window.$ = window.jQuery = global.$ = global.jQuery = $;

// start the Stimulus application
import './bootstrap';
import * as bootstrap from 'bootstrap';
import {includes} from "core-js/internals/array-includes";
window.bootstrap = bootstrap;



import moment from 'moment';
window.moment = moment;
const ko = require("knockout/build/output/knockout-latest");
window.ko = ko;
const daterangepicker = require('knockout-daterangepicker/dist/daterangepicker.js');
window.daterangepicker = daterangepicker;

window.addItemFormLink = function(obj) {
    console.log(obj);
}

$(document).ready(function() {
/*
Idea:
Multy uploading images to gallery
Entites:
Gallery
Image
GallerySettings

Every input file type element has data tag "options"
(check it out in templates/gallery/_form.html.twig)
<input type="file" data-options="[{"name":"small","width":"200","height":"200"},{"name":"middle","height":"200"}]"
options is json array.
When image loads has to be resize according every option parameter
TODO:
  create a block for resized image by params

 */
    $('.file_upload').on('click', function () {
        $.ajax({
            // Your server script to process the upload
            url: 'upload.php',
            type: 'POST',

            // Form data
            data: new FormData($('form')[0]),

            // Tell jQuery not to process data or worry about content-type
            // You *must* include these options!
            cache: false,
            contentType: false,
            processData: false,

            // Custom XMLHttpRequest
            xhr: function () {
                var myXhr = $.ajaxSettings.xhr();
                if (myXhr.upload) {
                    // For handling the progress of the upload
                    myXhr.upload.addEventListener('progress', function (e) {
                        if (e.lengthComputable) {
                            console.log({
                                value: e.loaded,
                                max: e.total,
                            });
                        }
                    }, false);
                }
                return myXhr;
            }
        });
    }).off('change').on('change', function() {

        var el = $(this).closest('.upload-preview').find(".images-preview");
        el.find("*").remove();


        var collectionHolderClass = $(this).data('collectionHolderClass');
        var previewWidth = $(this).data('previewWidt');
        var previewHeight = $(this).data('previewHeight');
        var template = $(this).data('template');

        console.log(collectionHolderClass);

        previewWidth = previewWidth === undefined || previewWidth == 'undefined' ? 300 : previewWidth;
        previewHeight = previewHeight === undefined || previewHeight == 'undefined'  ? 300 : previewHeight;
        template = template === undefined || template == 'undefined' ? "<canvas id='__CANVAS_ID_REPLACE__' style='border: 1px solid;'></canvas><br>" : template;

        for(var i = 0; i < this.files.length; i++) {

            var image = new Image();
            image.id = 'image_'+i;
            image.src = URL.createObjectURL(this.files[i]);

            image.onload = function(e) {
                var ratioW = previewWidth / this.width;  // Width ratio
                var ratioH = previewHeight / this.height;  // Height ratio

                // If height ratio is bigger then we need to scale height
                if(ratioH > ratioW) {
                    var newWidth = previewWidth
                    var newHeight = this.height * ratioW
                }
                else{
                    var newHeight = previewHeight
                    var newWidth = this.height * ratioH

                }

                var index = $('.image-item').length;
                var item = '';

                if (collectionHolderClass !== undefined && collectionHolderClass != 'undefined') {
                    item = new String($('#'+collectionHolderClass).data('prototype')).replace(/__name__/g, index);
                }

                if (item === undefined || item == 'undefined') {
                    item = '';
                }

                var canvasId = 'canvas_'+index;
                var deleteId = 'delete_'+index;

                el.append((new String(template)
                    .replace('__CANVAS_ID_REPLACE__', canvasId)
                    .replace('__FROM_REPLACE__', item)
                    .replace('__DELETE_BUTTON_ID_REPLACE__', deleteId)
                ));
                var canvas =  $('#'+canvasId).get(0);
                canvas.width = previewWidth;
                canvas.height = previewHeight;

                var ctx = canvas.getContext('2d')
                ctx.drawImage(e.target, 0, 0 / 2, newWidth, newHeight);

                $('#'+deleteId).on('click', function() {
                    $(this).closest('.card-item-block').remove();
                });
            }
        }
    });


    $('.add_item_link').on('click', function() {
        const collectionHolder = $(this).data('collectionHolderClass');

        const ulId = $(this).data('ulId');

        var index = $(this).data('index');

        const item = new String($('#'+collectionHolder).data('prototype')).replace(/__name__/g, index);

        $('#'+ulId).append(item);

        index++;
        $(this).data('index', index);

    });


    $('#page_filter_createdAt').daterangepicker({
        forceUpdate: true,

        callback: function(startDate, endDate, period){
            var title = startDate.format(conf.datetime_format) + ' – ' + endDate.format(conf.datetime_format);
            $(this).val(title)
        }
    });

    $('#page_filter_updatedAt').daterangepicker({
        forceUpdate: true,
        callback: function(startDate, endDate, period){
            var title = startDate.format(conf.datetime_format) + ' – ' + endDate.format(conf.datetime_format);
            $(this).val(title)
        }
    });

});


