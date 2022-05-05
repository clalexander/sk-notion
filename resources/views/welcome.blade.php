<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>

        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">

        <!-- Styles -->
        <style>
            /*! normalize.css v8.0.1 | MIT License | github.com/necolas/normalize.css */html{line-height:1.15;-webkit-text-size-adjust:100%}body{margin:0}a{background-color:transparent}[hidden]{display:none}html{font-family:system-ui,-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Helvetica Neue,Arial,Noto Sans,sans-serif,Apple Color Emoji,Segoe UI Emoji,Segoe UI Symbol,Noto Color Emoji;line-height:1.5}*,:after,:before{box-sizing:border-box;border:0 solid #e2e8f0}a{color:inherit;text-decoration:inherit}svg,video{display:block;vertical-align:middle}video{max-width:100%;height:auto}.bg-white{--bg-opacity:1;background-color:#fff;background-color:rgba(255,255,255,var(--bg-opacity))}.bg-gray-100{--bg-opacity:1;background-color:#f7fafc;background-color:rgba(247,250,252,var(--bg-opacity))}.border-gray-200{--border-opacity:1;border-color:#edf2f7;border-color:rgba(237,242,247,var(--border-opacity))}.border-t{border-top-width:1px}.flex{display:flex}.grid{display:grid}.hidden{display:none}.items-center{align-items:center}.justify-center{justify-content:center}.font-semibold{font-weight:600}.h-5{height:1.25rem}.h-8{height:2rem}.h-16{height:4rem}.text-sm{font-size:.875rem}.text-lg{font-size:1.125rem}.leading-7{line-height:1.75rem}.mx-auto{margin-left:auto;margin-right:auto}.ml-1{margin-left:.25rem}.mt-2{margin-top:.5rem}.mr-2{margin-right:.5rem}.ml-2{margin-left:.5rem}.mt-4{margin-top:1rem}.ml-4{margin-left:1rem}.mt-8{margin-top:2rem}.ml-12{margin-left:3rem}.-mt-px{margin-top:-1px}.max-w-6xl{max-width:72rem}.min-h-screen{min-height:100vh}.overflow-hidden{overflow:hidden}.p-6{padding:1.5rem}.py-4{padding-top:1rem;padding-bottom:1rem}.px-6{padding-left:1.5rem;padding-right:1.5rem}.pt-8{padding-top:2rem}.fixed{position:fixed}.relative{position:relative}.top-0{top:0}.right-0{right:0}.shadow{box-shadow:0 1px 3px 0 rgba(0,0,0,.1),0 1px 2px 0 rgba(0,0,0,.06)}.text-center{text-align:center}.text-gray-200{--text-opacity:1;color:#edf2f7;color:rgba(237,242,247,var(--text-opacity))}.text-gray-300{--text-opacity:1;color:#e2e8f0;color:rgba(226,232,240,var(--text-opacity))}.text-gray-400{--text-opacity:1;color:#cbd5e0;color:rgba(203,213,224,var(--text-opacity))}.text-gray-500{--text-opacity:1;color:#a0aec0;color:rgba(160,174,192,var(--text-opacity))}.text-gray-600{--text-opacity:1;color:#718096;color:rgba(113,128,150,var(--text-opacity))}.text-gray-700{--text-opacity:1;color:#4a5568;color:rgba(74,85,104,var(--text-opacity))}.text-gray-900{--text-opacity:1;color:#1a202c;color:rgba(26,32,44,var(--text-opacity))}.underline{text-decoration:underline}.antialiased{-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale}.w-5{width:1.25rem}.w-8{width:2rem}.w-auto{width:auto}.grid-cols-1{grid-template-columns:repeat(1,minmax(0,1fr))}@media (min-width:640px){.sm\:rounded-lg{border-radius:.5rem}.sm\:block{display:block}.sm\:items-center{align-items:center}.sm\:justify-start{justify-content:flex-start}.sm\:justify-between{justify-content:space-between}.sm\:h-20{height:5rem}.sm\:ml-0{margin-left:0}.sm\:px-6{padding-left:1.5rem;padding-right:1.5rem}.sm\:pt-0{padding-top:0}.sm\:text-left{text-align:left}.sm\:text-right{text-align:right}}@media (min-width:768px){.md\:border-t-0{border-top-width:0}.md\:border-l{border-left-width:1px}.md\:grid-cols-2{grid-template-columns:repeat(2,minmax(0,1fr))}}@media (min-width:1024px){.lg\:px-8{padding-left:2rem;padding-right:2rem}}@media (prefers-color-scheme:dark){.dark\:bg-gray-800{--bg-opacity:1;background-color:#2d3748;background-color:rgba(45,55,72,var(--bg-opacity))}.dark\:bg-gray-900{--bg-opacity:1;background-color:#1a202c;background-color:rgba(26,32,44,var(--bg-opacity))}.dark\:border-gray-700{--border-opacity:1;border-color:#4a5568;border-color:rgba(74,85,104,var(--border-opacity))}.dark\:text-white{--text-opacity:1;color:#fff;color:rgba(255,255,255,var(--text-opacity))}.dark\:text-gray-400{--text-opacity:1;color:#cbd5e0;color:rgba(203,213,224,var(--text-opacity))}}
        </style>

        <style>
            body {
                font-family: 'Nunito';
            }
        </style>
    </head>
    <body class="antialiased">
        <div class="relative flex items-top justify-center min-h-screen bg-gray-100 dark:bg-gray-900 sm:items-center sm:pt-0">

            <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
                <div class="">
                    <input type="text" id="field_name" placeholder="Field name">
                    <input type="text" id="keyword" placeholder="Keyword">
                    <button onClick="search()">Search</button>
                </div>
                <hr>
                <div class="">
                    <input type="text" id="block_id" placeholder="Block ID">
                    <button onClick="getFullContents()">Get Full Contents</button>
                </div>
                <hr>
                <div class="">
                    <input type="text" id="block_id2" placeholder="Block ID">
                    <select id="content_type">
                        <option value="Paragraph">Paragraph</option>
                        <option value="BulletedListItem">BulletedListItem</option>
                        <option value="HeadingOne">HeadingOne</option>
                        <option value="HeadingTwo">HeadingTwo</option>
                        <option value="HeadingThree">HeadingThree</option>
                        <option value="NumberedListItem">NumberedListItem</option>
                        <option value="ToDo">ToDo</option>
                        <option value="Toggle" disabled>Toggle</option>
                        <option value="Embed" disabled>Embed</option>
                        <option value="Image" disabled>Image</option>
                        <option value="File" disabled>File</option>
                        <option value="Video" disabled>Video</option>
                        <option value="Pdf" disabled>Pdf</option>
                    </select>
                    <input type="text" id="txt_contents" placeholder="Text Contents">
                    <button onClick="appendContents()">Append</button>
                </div>
                <hr>
                <div class="">
                    <input type="text" id="db_id" placeholder="Database ID">
                    <textarea id="page_options" placeholder="Page Options"></textarea>
                    <button onClick="insertPageToDB()">Insert</button>
                </div>
                <hr>
                <div class="">
                    <input type="text" id="page_id" placeholder="Page ID">
                    <input type="text" id="property_name" placeholder="Property Name">
                    <input type="text" id="property_value" placeholder="Property Value">
                    <button onClick="updatePageProperty()">Update</button>
                </div>
            </div>
        </div>
    </body>

    <script type="text/javascript">
        var page_json = {
            "Heading": "",
            "HeadingOrder": "",

            "Keywords": "",
            "Book": "",
            "Passage": "",
            "RelatedPassage": "",
            "BeginWord": "",
            "EndWord": "",
            "TextualBase": "",
            "Reference": "",

            "StartPage": "",
            "StartParagraph": "",
            "EndPage": "",
            "EndParagraph": "",

            "Category": [""],
            "VideoURL": "",
            "VideoTitle": "",
            "VideoTime": "",
            "Status": "",
            "NoteOrder": "",
            "Language": "",
            "BCV": ""
        }
        var page_json_txt = JSON.stringify(page_json)
        document.getElementById("page_options").value = page_json_txt

        function search() {
            var field_name = document.getElementById("field_name").value
            var keyword = document.getElementById("keyword").value
            var type = "db"
            var db_id = "ba2b8607fdb74c17b2ac971a5653be98"
            var params = {
                type: type,
                id: db_id,
                field_name: field_name,
                keyword: keyword
            }
            var urlParams = new URLSearchParams(params).toString()
            
            $.ajax({
                type: "GET",
                url: "/api/notion?" + urlParams,
                beforeSend: function (xhr) {
                    // dev
                    // xhr.setRequestHeader('Authorization', 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiYzkzN2E4Njc4ZTM0MGJiYzQyNDI0MDRlNmFmMjE0NmMzMTI5ZTQyNTBhNGQ0ZTkwYzM5Y2JjZjJjYTNkZjBjMTVkOGNiYmI0NzJiYjA1NmUiLCJpYXQiOjE2NDkyODY4MzguMTMzODU5LCJuYmYiOjE2NDkyODY4MzguMTMzODY0LCJleHAiOjE2ODA4MjI4MzguMDI2Nzg4LCJzdWIiOiIxIiwic2NvcGVzIjpbXX0.iUuh5mxovWNxTi6tqHqWXQVVwJ3vce5_taKOJdkC3ZBUts0FRpCHDu9SYzhlNETWDbQCrcfFPxaICVfKx2wX5KjFcapkhuUPRQlJnlZDnvONpYnX4eXAFAYv5giNEttc-JSOiIezKfUeO-xzRTADPd4BM-KFRsLTmeU13kvly6H2AHGxJBbaSjarnpuE5SmfDAENRsz9WAmfuKdTmtKBQgh3-yx2Z6Dhss9JHcmZP5-SHETnttOd-LA8VGxEhEJhPkYk8Ip9f86W0o1rD0yBu3MzxjeCN5ZhhXZjeC-R92O0ODOqobhEtZDj1H8Z2gUYrEAVMwwT1ggf7ysPYwLLh3idUarLsuJwxQ7qPOwaj8OsOUZtrXxxjH8jeZgqxxQl-66k3MDuPozxoradyjtNWQDJKKumC3-gASKsz9NuehTETXIbk6lfK6SVYuuZx558N5KcpfckHJxRlxueKrhIexwPKYR9-9iVFpfMae7Gs4RsPu17r_XRtQuw8dbtguI0tcRxE5tuM8M0DJ5gV_T3Im3Z53Vd3eWGvLugjErQ7UBecRfVMDWOV1RJi-ZuUJRKYXoDjfg-aAV58oYj84FKDXtuUwdMKAaUU1xFc2KAF3GslfL9dt5fMv-Ry1rdjGYgA7cxkDnRcYPyfsRkdh43JundKQM4zmEVBOyGXqS6ewE');

                    // live
                    xhr.setRequestHeader('Authorization', 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiMDcwMWE5YjZhODkyNDk3ZWM5OGYxNzEwZmY4ZTZkOGMxY2U3NTRhMTI0OGYzMzNmZWQyMTc3ZmY2NWFhZTk1ODg0ZTQwYTZjMTg1OTkzYjkiLCJpYXQiOjE2NDk5MDQ3MzguMTkwOTgyLCJuYmYiOjE2NDk5MDQ3MzguMTkwOTg4LCJleHAiOjE2ODE0NDA3MzguMTgxOTMsInN1YiI6IjEiLCJzY29wZXMiOltdfQ.MNybXOMEwyE3lqMIIQCjIhqtY_DFnZXr3uTvD7ZHqZpnciBWAvmNCaGBNAfrQKkGpV5oZTDfQB2bmJiY-MWb0y-QpNPC6ld5Cmq7PSV6AoUn3oWj1VYUrKTMN_8tWTyu65N13muT5aYT-IxrkZF4X4_0olyZLlBee8yG-cdktqFZ0jHCqsObf4T5YhP5IMJplQffZl7GjXK-xpGHYuIPgVyULJ9_DacpegyP2B-fKDzDeFmyxB10uyk670qi8KBV5u08_frt2NnQQBXtK2NmAiw847TEFXHhTE4rcswBRy-WAGTFQiwPCICse-PGV06zMZRXXS-lkz93w0hUe6DijXNrbIapP6VFzP_J2BfyPVuJjFfVL3GwEr2c5AGTl_hxTeawCjrrjEmbHUCO4CjXU2jKFl4rFidW4D3OCz9XuXInwMxaQmNPJIYMH7wkAwOQUHZaVC12EyXXTh1Fjthte5nwvCuvgeMzRpInIzxLp9buy9YfW1f32nTmTehbO6cKnLZu122Dj_IUR5eVmu_GVlVi_Lu9ReTUl-OAczqEHFOgtNPCX55kKvFqjJepDB42hR8R3pk9gR4N1JzzgFVpSS8DfsgEYZWoaTijm3EVaeqb80S5cCzlXXLDIVIlnEtr14Egq2NomAKmGwgtqUH3twAucLUH5cwPMA4h3stf9dQ');
                },
                contentType: "application/json; charset=utf-8",
                dataType : 'JSON',
                async: false,
            }).done(function(data) {
                console.log(data)
            })
        }

        function getFullContents() {
            var block_id = document.getElementById("block_id").value
            var type = "block"
            var params = {
                type: type,
                id: block_id,
                include_child: true
            }
            var urlParams = new URLSearchParams(params).toString()
            $.ajax({
                type: "GET",
                url: "/api/notion?" + urlParams,
                beforeSend: function (xhr) {
                    // dev
                    // xhr.setRequestHeader('Authorization', 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiYzkzN2E4Njc4ZTM0MGJiYzQyNDI0MDRlNmFmMjE0NmMzMTI5ZTQyNTBhNGQ0ZTkwYzM5Y2JjZjJjYTNkZjBjMTVkOGNiYmI0NzJiYjA1NmUiLCJpYXQiOjE2NDkyODY4MzguMTMzODU5LCJuYmYiOjE2NDkyODY4MzguMTMzODY0LCJleHAiOjE2ODA4MjI4MzguMDI2Nzg4LCJzdWIiOiIxIiwic2NvcGVzIjpbXX0.iUuh5mxovWNxTi6tqHqWXQVVwJ3vce5_taKOJdkC3ZBUts0FRpCHDu9SYzhlNETWDbQCrcfFPxaICVfKx2wX5KjFcapkhuUPRQlJnlZDnvONpYnX4eXAFAYv5giNEttc-JSOiIezKfUeO-xzRTADPd4BM-KFRsLTmeU13kvly6H2AHGxJBbaSjarnpuE5SmfDAENRsz9WAmfuKdTmtKBQgh3-yx2Z6Dhss9JHcmZP5-SHETnttOd-LA8VGxEhEJhPkYk8Ip9f86W0o1rD0yBu3MzxjeCN5ZhhXZjeC-R92O0ODOqobhEtZDj1H8Z2gUYrEAVMwwT1ggf7ysPYwLLh3idUarLsuJwxQ7qPOwaj8OsOUZtrXxxjH8jeZgqxxQl-66k3MDuPozxoradyjtNWQDJKKumC3-gASKsz9NuehTETXIbk6lfK6SVYuuZx558N5KcpfckHJxRlxueKrhIexwPKYR9-9iVFpfMae7Gs4RsPu17r_XRtQuw8dbtguI0tcRxE5tuM8M0DJ5gV_T3Im3Z53Vd3eWGvLugjErQ7UBecRfVMDWOV1RJi-ZuUJRKYXoDjfg-aAV58oYj84FKDXtuUwdMKAaUU1xFc2KAF3GslfL9dt5fMv-Ry1rdjGYgA7cxkDnRcYPyfsRkdh43JundKQM4zmEVBOyGXqS6ewE');

                    // live
                    xhr.setRequestHeader('Authorization', 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiMDcwMWE5YjZhODkyNDk3ZWM5OGYxNzEwZmY4ZTZkOGMxY2U3NTRhMTI0OGYzMzNmZWQyMTc3ZmY2NWFhZTk1ODg0ZTQwYTZjMTg1OTkzYjkiLCJpYXQiOjE2NDk5MDQ3MzguMTkwOTgyLCJuYmYiOjE2NDk5MDQ3MzguMTkwOTg4LCJleHAiOjE2ODE0NDA3MzguMTgxOTMsInN1YiI6IjEiLCJzY29wZXMiOltdfQ.MNybXOMEwyE3lqMIIQCjIhqtY_DFnZXr3uTvD7ZHqZpnciBWAvmNCaGBNAfrQKkGpV5oZTDfQB2bmJiY-MWb0y-QpNPC6ld5Cmq7PSV6AoUn3oWj1VYUrKTMN_8tWTyu65N13muT5aYT-IxrkZF4X4_0olyZLlBee8yG-cdktqFZ0jHCqsObf4T5YhP5IMJplQffZl7GjXK-xpGHYuIPgVyULJ9_DacpegyP2B-fKDzDeFmyxB10uyk670qi8KBV5u08_frt2NnQQBXtK2NmAiw847TEFXHhTE4rcswBRy-WAGTFQiwPCICse-PGV06zMZRXXS-lkz93w0hUe6DijXNrbIapP6VFzP_J2BfyPVuJjFfVL3GwEr2c5AGTl_hxTeawCjrrjEmbHUCO4CjXU2jKFl4rFidW4D3OCz9XuXInwMxaQmNPJIYMH7wkAwOQUHZaVC12EyXXTh1Fjthte5nwvCuvgeMzRpInIzxLp9buy9YfW1f32nTmTehbO6cKnLZu122Dj_IUR5eVmu_GVlVi_Lu9ReTUl-OAczqEHFOgtNPCX55kKvFqjJepDB42hR8R3pk9gR4N1JzzgFVpSS8DfsgEYZWoaTijm3EVaeqb80S5cCzlXXLDIVIlnEtr14Egq2NomAKmGwgtqUH3twAucLUH5cwPMA4h3stf9dQ');
                },
                contentType: "application/json; charset=utf-8",
                dataType : 'JSON',
                async: false,
            }).done(function(data) {
                console.log(data)
            })
        }

        function appendContents() {
            var block_id = document.getElementById("block_id2").value
            var contents = document.getElementById("txt_contents").value
            var content_type = document.getElementById("content_type").value
            var type = "block"
            var params = {
                type: type,
                id: block_id,
                content_type: content_type,
                contents: contents
            }
            console.log(params)
            // return;
            $.ajax({
                type: "POST",
                url: "/api/notion",
                beforeSend: function (xhr) {
                    // dev
                    // xhr.setRequestHeader('Authorization', 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiYzkzN2E4Njc4ZTM0MGJiYzQyNDI0MDRlNmFmMjE0NmMzMTI5ZTQyNTBhNGQ0ZTkwYzM5Y2JjZjJjYTNkZjBjMTVkOGNiYmI0NzJiYjA1NmUiLCJpYXQiOjE2NDkyODY4MzguMTMzODU5LCJuYmYiOjE2NDkyODY4MzguMTMzODY0LCJleHAiOjE2ODA4MjI4MzguMDI2Nzg4LCJzdWIiOiIxIiwic2NvcGVzIjpbXX0.iUuh5mxovWNxTi6tqHqWXQVVwJ3vce5_taKOJdkC3ZBUts0FRpCHDu9SYzhlNETWDbQCrcfFPxaICVfKx2wX5KjFcapkhuUPRQlJnlZDnvONpYnX4eXAFAYv5giNEttc-JSOiIezKfUeO-xzRTADPd4BM-KFRsLTmeU13kvly6H2AHGxJBbaSjarnpuE5SmfDAENRsz9WAmfuKdTmtKBQgh3-yx2Z6Dhss9JHcmZP5-SHETnttOd-LA8VGxEhEJhPkYk8Ip9f86W0o1rD0yBu3MzxjeCN5ZhhXZjeC-R92O0ODOqobhEtZDj1H8Z2gUYrEAVMwwT1ggf7ysPYwLLh3idUarLsuJwxQ7qPOwaj8OsOUZtrXxxjH8jeZgqxxQl-66k3MDuPozxoradyjtNWQDJKKumC3-gASKsz9NuehTETXIbk6lfK6SVYuuZx558N5KcpfckHJxRlxueKrhIexwPKYR9-9iVFpfMae7Gs4RsPu17r_XRtQuw8dbtguI0tcRxE5tuM8M0DJ5gV_T3Im3Z53Vd3eWGvLugjErQ7UBecRfVMDWOV1RJi-ZuUJRKYXoDjfg-aAV58oYj84FKDXtuUwdMKAaUU1xFc2KAF3GslfL9dt5fMv-Ry1rdjGYgA7cxkDnRcYPyfsRkdh43JundKQM4zmEVBOyGXqS6ewE');

                    // live
                    xhr.setRequestHeader('Authorization', 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiMDcwMWE5YjZhODkyNDk3ZWM5OGYxNzEwZmY4ZTZkOGMxY2U3NTRhMTI0OGYzMzNmZWQyMTc3ZmY2NWFhZTk1ODg0ZTQwYTZjMTg1OTkzYjkiLCJpYXQiOjE2NDk5MDQ3MzguMTkwOTgyLCJuYmYiOjE2NDk5MDQ3MzguMTkwOTg4LCJleHAiOjE2ODE0NDA3MzguMTgxOTMsInN1YiI6IjEiLCJzY29wZXMiOltdfQ.MNybXOMEwyE3lqMIIQCjIhqtY_DFnZXr3uTvD7ZHqZpnciBWAvmNCaGBNAfrQKkGpV5oZTDfQB2bmJiY-MWb0y-QpNPC6ld5Cmq7PSV6AoUn3oWj1VYUrKTMN_8tWTyu65N13muT5aYT-IxrkZF4X4_0olyZLlBee8yG-cdktqFZ0jHCqsObf4T5YhP5IMJplQffZl7GjXK-xpGHYuIPgVyULJ9_DacpegyP2B-fKDzDeFmyxB10uyk670qi8KBV5u08_frt2NnQQBXtK2NmAiw847TEFXHhTE4rcswBRy-WAGTFQiwPCICse-PGV06zMZRXXS-lkz93w0hUe6DijXNrbIapP6VFzP_J2BfyPVuJjFfVL3GwEr2c5AGTl_hxTeawCjrrjEmbHUCO4CjXU2jKFl4rFidW4D3OCz9XuXInwMxaQmNPJIYMH7wkAwOQUHZaVC12EyXXTh1Fjthte5nwvCuvgeMzRpInIzxLp9buy9YfW1f32nTmTehbO6cKnLZu122Dj_IUR5eVmu_GVlVi_Lu9ReTUl-OAczqEHFOgtNPCX55kKvFqjJepDB42hR8R3pk9gR4N1JzzgFVpSS8DfsgEYZWoaTijm3EVaeqb80S5cCzlXXLDIVIlnEtr14Egq2NomAKmGwgtqUH3twAucLUH5cwPMA4h3stf9dQ');
                },
                contentType: "application/json; charset=utf-8",
                dataType : 'JSON',
                data: JSON.stringify(params),
                async: false,
            }).done(function(data) {
                console.log(data)
            })
        }

        function insertPageToDB() {
            var db_id = document.getElementById("db_id").value
            var options = JSON.parse(document.getElementById("page_options").value)
            var type = "db"
            var params = {
                type: type,
                id: db_id,
                options: options
            }
            $.ajax({
                type: "POST",
                url: "/api/notion",
                beforeSend: function (xhr) {
                    // dev
                    // xhr.setRequestHeader('Authorization', 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiYzkzN2E4Njc4ZTM0MGJiYzQyNDI0MDRlNmFmMjE0NmMzMTI5ZTQyNTBhNGQ0ZTkwYzM5Y2JjZjJjYTNkZjBjMTVkOGNiYmI0NzJiYjA1NmUiLCJpYXQiOjE2NDkyODY4MzguMTMzODU5LCJuYmYiOjE2NDkyODY4MzguMTMzODY0LCJleHAiOjE2ODA4MjI4MzguMDI2Nzg4LCJzdWIiOiIxIiwic2NvcGVzIjpbXX0.iUuh5mxovWNxTi6tqHqWXQVVwJ3vce5_taKOJdkC3ZBUts0FRpCHDu9SYzhlNETWDbQCrcfFPxaICVfKx2wX5KjFcapkhuUPRQlJnlZDnvONpYnX4eXAFAYv5giNEttc-JSOiIezKfUeO-xzRTADPd4BM-KFRsLTmeU13kvly6H2AHGxJBbaSjarnpuE5SmfDAENRsz9WAmfuKdTmtKBQgh3-yx2Z6Dhss9JHcmZP5-SHETnttOd-LA8VGxEhEJhPkYk8Ip9f86W0o1rD0yBu3MzxjeCN5ZhhXZjeC-R92O0ODOqobhEtZDj1H8Z2gUYrEAVMwwT1ggf7ysPYwLLh3idUarLsuJwxQ7qPOwaj8OsOUZtrXxxjH8jeZgqxxQl-66k3MDuPozxoradyjtNWQDJKKumC3-gASKsz9NuehTETXIbk6lfK6SVYuuZx558N5KcpfckHJxRlxueKrhIexwPKYR9-9iVFpfMae7Gs4RsPu17r_XRtQuw8dbtguI0tcRxE5tuM8M0DJ5gV_T3Im3Z53Vd3eWGvLugjErQ7UBecRfVMDWOV1RJi-ZuUJRKYXoDjfg-aAV58oYj84FKDXtuUwdMKAaUU1xFc2KAF3GslfL9dt5fMv-Ry1rdjGYgA7cxkDnRcYPyfsRkdh43JundKQM4zmEVBOyGXqS6ewE');

                    // live
                    xhr.setRequestHeader('Authorization', 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiMDcwMWE5YjZhODkyNDk3ZWM5OGYxNzEwZmY4ZTZkOGMxY2U3NTRhMTI0OGYzMzNmZWQyMTc3ZmY2NWFhZTk1ODg0ZTQwYTZjMTg1OTkzYjkiLCJpYXQiOjE2NDk5MDQ3MzguMTkwOTgyLCJuYmYiOjE2NDk5MDQ3MzguMTkwOTg4LCJleHAiOjE2ODE0NDA3MzguMTgxOTMsInN1YiI6IjEiLCJzY29wZXMiOltdfQ.MNybXOMEwyE3lqMIIQCjIhqtY_DFnZXr3uTvD7ZHqZpnciBWAvmNCaGBNAfrQKkGpV5oZTDfQB2bmJiY-MWb0y-QpNPC6ld5Cmq7PSV6AoUn3oWj1VYUrKTMN_8tWTyu65N13muT5aYT-IxrkZF4X4_0olyZLlBee8yG-cdktqFZ0jHCqsObf4T5YhP5IMJplQffZl7GjXK-xpGHYuIPgVyULJ9_DacpegyP2B-fKDzDeFmyxB10uyk670qi8KBV5u08_frt2NnQQBXtK2NmAiw847TEFXHhTE4rcswBRy-WAGTFQiwPCICse-PGV06zMZRXXS-lkz93w0hUe6DijXNrbIapP6VFzP_J2BfyPVuJjFfVL3GwEr2c5AGTl_hxTeawCjrrjEmbHUCO4CjXU2jKFl4rFidW4D3OCz9XuXInwMxaQmNPJIYMH7wkAwOQUHZaVC12EyXXTh1Fjthte5nwvCuvgeMzRpInIzxLp9buy9YfW1f32nTmTehbO6cKnLZu122Dj_IUR5eVmu_GVlVi_Lu9ReTUl-OAczqEHFOgtNPCX55kKvFqjJepDB42hR8R3pk9gR4N1JzzgFVpSS8DfsgEYZWoaTijm3EVaeqb80S5cCzlXXLDIVIlnEtr14Egq2NomAKmGwgtqUH3twAucLUH5cwPMA4h3stf9dQ');
                },
                contentType: "application/json; charset=utf-8",
                dataType : 'JSON',
                data: JSON.stringify(params),
                async: false,
            })
        }

        function updatePageProperty() {var db_id = document.getElementById("db_id").value
            var page_id = document.getElementById("page_id").value
            var property_name = document.getElementById("property_name").value
            var property_value = document.getElementById("property_value").value
            var type = "page"
            var params = {
                type: type,
                id: page_id,
                property_name: property_name,
                property_value: property_value
            }
            $.ajax({
                type: "PUT",
                url: "/api/notion/" + page_id,
                beforeSend: function (xhr) {
                    // dev
                    // xhr.setRequestHeader('Authorization', 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiYzkzN2E4Njc4ZTM0MGJiYzQyNDI0MDRlNmFmMjE0NmMzMTI5ZTQyNTBhNGQ0ZTkwYzM5Y2JjZjJjYTNkZjBjMTVkOGNiYmI0NzJiYjA1NmUiLCJpYXQiOjE2NDkyODY4MzguMTMzODU5LCJuYmYiOjE2NDkyODY4MzguMTMzODY0LCJleHAiOjE2ODA4MjI4MzguMDI2Nzg4LCJzdWIiOiIxIiwic2NvcGVzIjpbXX0.iUuh5mxovWNxTi6tqHqWXQVVwJ3vce5_taKOJdkC3ZBUts0FRpCHDu9SYzhlNETWDbQCrcfFPxaICVfKx2wX5KjFcapkhuUPRQlJnlZDnvONpYnX4eXAFAYv5giNEttc-JSOiIezKfUeO-xzRTADPd4BM-KFRsLTmeU13kvly6H2AHGxJBbaSjarnpuE5SmfDAENRsz9WAmfuKdTmtKBQgh3-yx2Z6Dhss9JHcmZP5-SHETnttOd-LA8VGxEhEJhPkYk8Ip9f86W0o1rD0yBu3MzxjeCN5ZhhXZjeC-R92O0ODOqobhEtZDj1H8Z2gUYrEAVMwwT1ggf7ysPYwLLh3idUarLsuJwxQ7qPOwaj8OsOUZtrXxxjH8jeZgqxxQl-66k3MDuPozxoradyjtNWQDJKKumC3-gASKsz9NuehTETXIbk6lfK6SVYuuZx558N5KcpfckHJxRlxueKrhIexwPKYR9-9iVFpfMae7Gs4RsPu17r_XRtQuw8dbtguI0tcRxE5tuM8M0DJ5gV_T3Im3Z53Vd3eWGvLugjErQ7UBecRfVMDWOV1RJi-ZuUJRKYXoDjfg-aAV58oYj84FKDXtuUwdMKAaUU1xFc2KAF3GslfL9dt5fMv-Ry1rdjGYgA7cxkDnRcYPyfsRkdh43JundKQM4zmEVBOyGXqS6ewE');

                    // live
                    xhr.setRequestHeader('Authorization', 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiMDcwMWE5YjZhODkyNDk3ZWM5OGYxNzEwZmY4ZTZkOGMxY2U3NTRhMTI0OGYzMzNmZWQyMTc3ZmY2NWFhZTk1ODg0ZTQwYTZjMTg1OTkzYjkiLCJpYXQiOjE2NDk5MDQ3MzguMTkwOTgyLCJuYmYiOjE2NDk5MDQ3MzguMTkwOTg4LCJleHAiOjE2ODE0NDA3MzguMTgxOTMsInN1YiI6IjEiLCJzY29wZXMiOltdfQ.MNybXOMEwyE3lqMIIQCjIhqtY_DFnZXr3uTvD7ZHqZpnciBWAvmNCaGBNAfrQKkGpV5oZTDfQB2bmJiY-MWb0y-QpNPC6ld5Cmq7PSV6AoUn3oWj1VYUrKTMN_8tWTyu65N13muT5aYT-IxrkZF4X4_0olyZLlBee8yG-cdktqFZ0jHCqsObf4T5YhP5IMJplQffZl7GjXK-xpGHYuIPgVyULJ9_DacpegyP2B-fKDzDeFmyxB10uyk670qi8KBV5u08_frt2NnQQBXtK2NmAiw847TEFXHhTE4rcswBRy-WAGTFQiwPCICse-PGV06zMZRXXS-lkz93w0hUe6DijXNrbIapP6VFzP_J2BfyPVuJjFfVL3GwEr2c5AGTl_hxTeawCjrrjEmbHUCO4CjXU2jKFl4rFidW4D3OCz9XuXInwMxaQmNPJIYMH7wkAwOQUHZaVC12EyXXTh1Fjthte5nwvCuvgeMzRpInIzxLp9buy9YfW1f32nTmTehbO6cKnLZu122Dj_IUR5eVmu_GVlVi_Lu9ReTUl-OAczqEHFOgtNPCX55kKvFqjJepDB42hR8R3pk9gR4N1JzzgFVpSS8DfsgEYZWoaTijm3EVaeqb80S5cCzlXXLDIVIlnEtr14Egq2NomAKmGwgtqUH3twAucLUH5cwPMA4h3stf9dQ');
                },
                contentType: "application/json; charset=utf-8",
                dataType : 'JSON',
                data: JSON.stringify(params),
                async: false,
            })
        }
    </script>
</html>
