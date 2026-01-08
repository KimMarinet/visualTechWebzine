/* Write Post Page Logic with TOAST UI Editor & Attachments */
$(document).ready(function () {

    // Check for Edit Mode
    const urlParams = new URLSearchParams(window.location.search);
    const editPostId = urlParams.get('id');
    let isEditMode = !!editPostId;

    // Initialize TOAST UI Editor
    const Editor = toastui.Editor;
    const editor = new Editor({
        el: document.querySelector('#editor'),
        height: '500px',
        initialEditType: 'wysiwyg',
        previewStyle: 'vertical',
        toolbarItems: [
            ['heading', 'bold', 'italic', 'strike'],
            ['hr', 'quote'],
            ['ul', 'ol', 'task', 'indent', 'outdent'],
            ['table', 'image', 'link'],
            ['code', 'codeblock']
        ],
        hooks: {
            addImageBlobHook: (blob, callback) => {
                uploadFile(blob, callback);
                return false; // Prevent default processing
            }
        }
    });

    // Helper to format file size
    function formatBytes(bytes, decimals = 2) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const dm = decimals < 0 ? 0 : decimals;
        const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
    }

    // Attachment List UI Manager (Keep existing)
    const attachmentManager = {
        list: $('#attachment-list'),
        emptyMsg: $('#attachment-list .empty-message'),

        startUpload: function (file, tempId) {
            this.emptyMsg.hide();

            const li = $('<li>')
                .attr('id', 'item-' + tempId)
                .css({
                    'display': 'flex',
                    'justify-content': 'space-between',
                    'align-items': 'center',
                    'padding': '8px 0',
                    'border-bottom': '1px solid #eee'
                });

            const info = $('<div>').css('flex-grow', '1');
            const nameSpan = $('<strong>').text(file.name + ' ');
            const statusSpan = $('<span>')
                .addClass('upload-status')
                .css({ 'color': '#2196F3', 'font-size': '0.9em' })
                .text('[Uploading 0%...]');

            info.append(nameSpan).append(statusSpan);
            li.append(info);
            this.list.append(li);
        },

        updateProgress: function (tempId, percent) {
            const li = this.list.find('#item-' + tempId);
            if (li.length) {
                li.find('.upload-status').text(`[Uploading ${Math.round(percent)}%...]`);
            }
        },

        completeUpload: function (tempId, file, uniqueId, url, type) {
            const li = this.list.find('#item-' + tempId);
            if (!li.length) return;

            li.attr('data-editor-alt', uniqueId);

            const info = li.find('div');
            info.empty();
            info.append($('<strong>').text(file.name));
            info.append($('<span style="color:#666; font-size:0.9em; margin-left:8px;">').text('(' + formatBytes(file.size) + ')'));

            const deleteBtn = $('<button>')
                .text('X')
                .attr('type', 'button')
                .css({
                    'background': '#ff4444',
                    'color': 'white',
                    'border': 'none',
                    'border-radius': '50%',
                    'width': '24px',
                    'height': '24px',
                    'cursor': 'pointer',
                    'margin-left': '10px'
                })
                .on('click', function () {
                    li.remove();
                    if ($('#attachment-list li').length <= 1) {
                        if ($('#attachment-list li:not(.empty-message)').length === 0) {
                            attachmentManager.emptyMsg.show();
                        }
                    }

                    // Remove from Editor
                    let html = editor.getHTML();
                    const escapedUrl = url.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
                    const imgRegex = new RegExp(`<img[^>]*src="${escapedUrl}"[^>]*>`, 'g');
                    let newHtml = html.replace(imgRegex, '');
                    editor.setHTML(newHtml);
                });

            li.append(deleteBtn);
        },

        failUpload: function (tempId, message) {
            const li = this.list.find('#item-' + tempId);
            if (li.length) {
                const status = li.find('.upload-status');
                status.css('color', 'red').text('[Failed: ' + message + ']');
                const closeBtn = $('<button>')
                    .text('X')
                    .attr('type', 'button')
                    .css({ 'background': '#ccc', 'border': 'none', 'cursor': 'pointer', 'margin-left': '10px' })
                    .on('click', function () { li.remove(); });
                li.append(closeBtn);
            }
        }
    };

    // Upload Logic via Toast UI Hook
    function uploadFile(file, callback) {
        if (!file.name) {
            file.name = "image_" + Date.now() + "." + (file.type.split('/')[1] || 'png');
        }

        const tempId = Date.now() + Math.floor(Math.random() * 1000);
        attachmentManager.startUpload(file, tempId);

        const formData = new FormData();
        formData.append('image', file);

        const isVideo = file.type.startsWith('video/');

        $.ajax({
            url: '../upload_temp.php',
            method: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            xhr: function () {
                const xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener("progress", function (evt) {
                    if (evt.lengthComputable) {
                        const percentComplete = (evt.loaded / evt.total) * 100;
                        attachmentManager.updateProgress(tempId, percentComplete);

                        const progressBar = $('#upload-progress-bar');
                        if (progressBar.length) {
                            $('#upload-progress-container').show();
                            progressBar.css('width', percentComplete + '%');
                        }
                    }
                }, false);
                return xhr;
            },
            success: function (response) {
                $('#upload-progress-container').hide();

                if (typeof response === 'string') {
                    try {
                        response = JSON.parse(response);
                    } catch (e) {
                        attachmentManager.failUpload(tempId, "Invalid Server Response");
                        return;
                    }
                }

                if (response.success) {
                    const cleanedPath = response.url.startsWith('/') ? response.url.substring(1) : response.url;
                    const imageUrl = '../../' + cleanedPath;
                    const uniqueId = 'media-' + tempId;

                    if (isVideo) {
                        callback(imageUrl, file.name);
                    } else {
                        callback(imageUrl, file.name);
                    }

                    attachmentManager.completeUpload(tempId, file, uniqueId, imageUrl, isVideo ? 'video' : 'image');

                } else {
                    attachmentManager.failUpload(tempId, response.message || 'Error');
                }
            },
            error: function (xhr, status, error) {
                $('#upload-progress-container').hide();
                attachmentManager.failUpload(tempId, "Network Error");
            }
        });
    }

    // Load Existing Data if Edit Mode
    if (isEditMode) {
        // Change UI Title
        $('h2.page-title').text('Edit Post');
        $('#submitBtn').text('Update Post');

        // Fetch Post Data
        fetch(`../posts.php`) // posts.php returns all? We need to filter or use a specific get API.
        // posts.php supports ?page=... but not single ID?
        // Wait, view.php loads single post via DB logic directly.
        // We can create a simple get_post_json.php or reuse existing mechanism.
        // Let's modify valid fetch. We can iterate posts.json result or use a dedicated script.
        // Or view.php itself might return JSON if we modify it? No, view.php is template.

        // Quick solution: Create a new tiny PHP script to get single post JSON, or use a loop on existing posts.php response?
        // posts.php has pagination. It might miss the post if it's old.
        // Let's add simple GET logic to 'api/get_post.php' OR just assume we can fetch it.
        // Better: Use `api/get_post.php` (new) or hack it.
        // I will implement a fetch call to a new `get_post.php`.

        fetch(`../get_post.php?id=${editPostId}`)
            .then(res => res.json())
            .then(data => {
                if (data.error) {
                    alert(data.message);
                    return;
                }
                const post = data;
                $('#title').val(post.title);
                $('#author').val(post.author);
                $('#summary').val(post.summary);
                $('#category').val(post.category);
                editor.setHTML(post.content);
            })
            .catch(err => console.error("Failed to load post", err));
    }

    // Submit Form
    const writeForm = document.getElementById('writeForm');
    if (writeForm) {
        writeForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const content = editor.getHTML();
            const title = document.getElementById('title').value.trim();
            const author = document.getElementById('author').value.trim();
            const summary = document.getElementById('summary').value.trim();
            const category = document.getElementById('category').value;

            const missingFields = [];
            if (!title) missingFields.push('제목');
            if (!author) missingFields.push('작성자');

            const cleanContent = content.replace(/(<([^>]+)>)/gi, "").trim();
            const hasMedia = content.indexOf('<img') !== -1 || content.indexOf('<video') !== -1;

            if (!cleanContent && !hasMedia) {
                missingFields.push('내용');
            }

            if (missingFields.length > 0) {
                alert(`다음 필수 내용을 작성해 주세요: ${missingFields.join(', ')}`);
                return;
            }

            const postData = {
                title: title,
                author: author,
                summary: summary ? summary : '',
                category: category,
                content: content
            };

            if (isEditMode) {
                postData.seq = editPostId;
            }

            const submitBtn = document.getElementById('submitBtn');
            const originalBtnText = submitBtn.textContent;
            submitBtn.textContent = 'Processing...';
            submitBtn.disabled = true;

            const targetUrl = isEditMode ? '../update_post.php' : '../create_post.php';

            try {
                const response = await fetch(targetUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(postData)
                });

                const result = await response.json();

                if (response.ok) {
                    alert(isEditMode ? 'Post updated successfully!' : 'Post published successfully!');
                    window.location.href = '../../index.html';
                } else {
                    throw new Error(result.message || 'Server returned error');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred: ' + error.message);
                submitBtn.textContent = originalBtnText;
                submitBtn.disabled = false;
            }
        });
    }
});
