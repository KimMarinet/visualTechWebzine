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
    <script src="../../js/board/write/write.js"></script>
</head>

<body>
    <header class="main-header">
        <div class="container header-container">
            <h1 class="logo">VisuaL<span class="highlight">Tech</span></h1>
            <nav class="main-nav">
                <a href="index.html" class="nav-link">Home</a>
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

                <div class="form-group">
                    <label class="form-label">Content</label>
                    <div class="editor-toolbar">
                        <button type="button" class="toolbar-btn" id="uploadImageBtn">Insert Image</button>
                    </div>

                    <div id="visualEditor" class="editor-content" contenteditable="true"></div>

                    <div class="uploaded-images-section">
                        <h4>Uploaded Images</h4>
                        <div id="imageList" class="image-list"></div>
                    </div>

                    <input type="file" id="imageInput" accept="image/*">
                </div>

                <div class="form-footer">
                    <button type="submit" class="submit-btn" id="submitBtn">Publish Post</button>
                </div>
            </form>
        </div>
    </main>


</body>

</html>