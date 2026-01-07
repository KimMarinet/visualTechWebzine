/* Write Post Page Logic */
document.addEventListener('DOMContentLoaded', () => {
    const visualEditor = document.getElementById('visualEditor');
    const uploadImageBtn = document.getElementById('uploadImageBtn');
    const imageInput = document.getElementById('imageInput');
    const writeForm = document.getElementById('writeForm');
    const imageList = document.getElementById('imageList');

    // Image Upload
    if (uploadImageBtn && imageInput) {
        uploadImageBtn.addEventListener('click', () => {
            imageInput.click();
        });

        imageInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    visualEditor.focus();

                    // Insert at cursor position if possible
                    const selection = window.getSelection();
                    if (selection.rangeCount > 0 && selection.getRangeAt(0).commonAncestorContainer.parentNode === visualEditor) {
                        const range = selection.getRangeAt(0);
                        range.deleteContents();
                        range.insertNode(img);
                    } else {
                        visualEditor.appendChild(img);
                    }

                    // Add newline after image
                    visualEditor.appendChild(document.createElement('br'));

                    // Add to image list
                    const div = document.createElement('div');
                    div.className = 'image-item';
                    const listImg = document.createElement('img');
                    listImg.src = e.target.result;
                    div.appendChild(listImg);
                    imageList.appendChild(div);
                };
                reader.readAsDataURL(file);
            }
            // Reset input
            imageInput.value = '';
        });
    }

    // Submit Form
    if (writeForm) {
        writeForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const title = document.getElementById('title').value.trim();
            const author = document.getElementById('author').value.trim();
            const summary = document.getElementById('summary').value.trim();
            const category = document.getElementById('category').value;

            // Content is only from visual editor now
            let content = visualEditor.innerHTML;

            // Basic content validation
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = content;
            const textContent = tempDiv.textContent.trim();
            const hasImages = tempDiv.getElementsByTagName('img').length > 0;

            const missingFields = [];
            if (!title) missingFields.push('제목');
            if (!author) missingFields.push('작성자');
            if (!textContent && !hasImages) missingFields.push('내용');

            if (missingFields.length > 0) {
                alert(`다음 필수 내용을 작성해 주세요: ${missingFields.join(', ')}`);
                return;
            }

            const postData = {
                title: title,
                author: author,
                // If summary is empty, it stays empty string
                summary: summary ? summary : '',
                category: category,
                content: content
            };

            const submitBtn = document.getElementById('submitBtn');
            submitBtn.textContent = 'Publishing...';
            submitBtn.disabled = true;

            try {
                const response = await fetch('../create_post.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(postData)
                });

                const result = await response.json();

                if (response.ok) {
                    alert('Post published successfully!');
                    window.location.href = '../../index.html';
                } else {
                    alert('Error: ' + result.message);
                    submitBtn.textContent = 'Publish Post';
                    submitBtn.disabled = false;
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred while saving the post.');
                submitBtn.textContent = 'Publish Post';
                submitBtn.disabled = false;
            }
        });
    }
});
