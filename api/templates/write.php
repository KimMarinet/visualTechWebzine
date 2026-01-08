<!DOCTYPE html>
<html lang="ko">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>포스트 작성</title>
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/board/write/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Lora:ital,wght@0,400;0,500;0,600;1,400&display=swap"
        rel="stylesheet">
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
            <div class="write-header">
                <a href="../../index.html" class="btn-back-arrow" aria-label="Go Back">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                </a>
                <h2>포스트 <span style="color: var(--primary);">작성</span></h2>
            </div>
            <form id="writeForm">
                <div class="form-row">
                    <div class="col-8">
                        <div class="form-group">
                            <label class="form-label">제목</label>
                            <input type="text" id="title" class="form-input" placeholder="제목을 입력해주세요">
                        </div>
                    </div>
                    <div class="col-2">
                        <div class="form-group">
                            <label class="form-label">작성자</label>
                            <input type="text" id="author" class="form-input" placeholder="작성자">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">요약</label>
                    <input type="text" id="summary" class="form-input" placeholder="요약 • 부제를 입력해주세요">
                </div>

                <div class="form-group">
                    <label class="form-label">카테고리</label>
                    <select id="category" class="form-input">
                        <option value="Tech">Tech</option>
                        <option value="Design">Design</option>
                        <option value="Lifestyle">Lifestyle</option>
                        <option value="Inspiration">Inspiration</option>
                    </select>
                </div>

                <!-- Attachment Area -->
                <div class="form-group">
                    <label class="form-label">첨부 파일</label>
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
                    <div id="editor"></div>
                </div>

                <div class="form-footer">
                    <button type="submit" class="submit-btn" id="submitBtn">게시글 작성</button>
                </div>
            </form>
        </div>
    </main>


</body>

</html>