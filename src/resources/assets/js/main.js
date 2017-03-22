(function($) {
    $.trumbowyg.svgPath = APP.asset_path + '/vendor/pilot/img/trumbowyg.svg'
    $textarea = $('.form-group textarea.wysywig')
    $textarea.trumbowyg({
        btnsDef: {
            image: {
                dropdown: ['insertImage', 'upload'],
                ico: 'insertImage'
            }
        },
        btns: [
            ['viewHTML'],
            ['formatting'],
            'btnGrp-semantic',
            ['superscript', 'subscript'],
            ['link'],
            ['image'],
            'btnGrp-justify',
            'btnGrp-lists',
            ['horizontalRule'],
            ['removeformat'],
            ['fullscreen']
        ],
        lang: 'fr',
        plugins: {
            // Add image parameters to upload plugin
            upload: {
                serverPath: APP.upload_url,
                fileFieldName: 'image',
                data:[{name: 'file_src', value: 'wysywig'}],
                headers: {
                    'X-CSRF-TOKEN': APP.csrf_token
                },
                urlPropertyName: 'data.link'
            }
        }

    })
}(jQuery))
