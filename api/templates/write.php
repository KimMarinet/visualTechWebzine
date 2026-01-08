<!DOCTYPE html>
<html lang="ko">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Write Post - VisuaLTech</title>
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/board/write/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- jQuery (Full version for Ajax support) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <!-- TOAST UI Editor -->
    <link rel="stylesheet" href="https://uicdn.toast.com/editor/latest/toastui-editor.min.css" />
    <script src="https://uicdn.toast.com/editor/latest/toastui-editor-all.min.js"></script>
    <script src="../../js/board/write/write.js"></script>
</head>

<body>
    <header class="main-header">
        <div class="container header-container">
            <h1 class="logo">VisuaL<span class="highlight">Tech</span></h1>
            <nav class="main-nav">
                <a href="../../index.html" class="nav-link">Home</a>
                <a href="#" class="nav-link">Trending</a>
                <a href="#" class="nav-link">Categories</a>
            </nav>
        </div>
    </header>

    <main class="container">
        <div class="write-container">
            <h2 style="margin-bottom: 2rem;">Write New Post</h2>

            <form id="writeForm">
                <div class="form-row">
                    <div class="col-8">
                        <div class="form-group">
                            <label class="form-label">Title</label>
                            <input type="text" id="title" class="form-input" placeholder="Enter post title">
                        </div>
                    </div>
                    <div class="col-2">
                        <div class="form-group">
                            <label class="form-label">Author</label>
                            <input type="text" id="author" class="form-input" placeholder="Your name">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Summary / Subtitle</label>
                    <input type="text" id="summary" class="form-input" placeholder="Enter a brief summary or subtitle">
                </div>

                <div class="form-group">
                    <label class="form-label">Category</label>
                    <select id="category" class="form-input">
                        <option value="Tech">Tech</option>
                        <option value="Design">Design</option>
                        <option value="Lifestyle">Lifestyle</option>
                        <option value="Inspiration">Inspiration</option>
                    </select>
                </div>

                <!-- Attachment Area -->
                <div class="form-group">
                    <label class="form-label">Attachments</label>
                    <div id="upload-progress-container" style="display:none; margin-bottom: 10px;">
                        <div style="background:#f3f3f3; border-radius:4px; overflow:hidden;">
                            <div id="upload-progress-bar"
                                style="width:0%; height:10px; background:#4CAF50; transition:width 0.3s;"></div>
                        </div>
                        <small id="upload-status-text">Uploading...</small>
                    </div>
                    <ul id="attachment-list" class="attachment-list"
                        style="list-style:none; padding:0; margin-bottom:15px; border:1px solid #ddd; padding:10px; border-radius:4px; min-height:50px; background:#fafafa;">
                        <li class="empty-message" style="color:#888; text-align:center;">No files attached.</li>
                    </ul>
                </div>

                <div class="form-group">
                    <label class="form-label">Content</label>
                    <div id="editor"></div>
                </div>

                <div class="form-footer">
                    <button type="submit" class="submit-btn" id="submitBtn">Publish Post</button>
                </div>
            </form>
        </div>
    </main>


</body>

</html>