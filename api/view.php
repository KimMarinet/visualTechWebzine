<?php
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
// Adjusted path: already in api/ so just point to data/posts.json
$posts = json_decode(file_get_contents(__DIR__ . '/data/posts.json'), true);
$post = null;

if ($posts) {
    foreach ($posts as $p) {
        if ($p['id'] === $id) {
            $post = $p;
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ko">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php echo $post ? htmlspecialchars($post['title']) : 'Post Not Found'; ?> - VisuaLTech
    </title>
    <!-- Adjusted path: go up one level to css/ -->
    <link rel="stylesheet" href="../css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .post-detail {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem 0;
        }

        .post-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .post-header h1 {
            font-size: 2.5rem;
            margin: 1rem 0;
            color: var(--text-dark);
        }

        .meta-info {
            color: var(--text-gray);
            font-size: 0.9rem;
        }

        .post-image-container img {
            width: 100%;
            height: auto;
            border-radius: 12px;
            margin-bottom: 2rem;
        }

        .post-content {
            font-size: 1.1rem;
            line-height: 1.8;
            color: var(--text-dark);
        }

        .post-content p {
            margin-bottom: 1.5rem;
        }

        .summary {
            font-size: 1.25rem;
            font-weight: 500;
            color: var(--text-dark);
            border-left: 4px solid var(--primary-color);
            padding-left: 1rem;
        }

        .btn-back {
            display: inline-block;
            margin-top: 2rem;
            text-decoration: none;
            color: var(--primary-color);
            font-weight: 600;
        }
    </style>
</head>

<body>
    <header class="main-header">
        <div class="container header-container">
            <h1 class="logo">VisuaL<span class="highlight">Tech</span></h1>
            <nav class="main-nav">
                <!-- Adjusted path: go up one level to index.html -->
                <a href="../index.html" class="nav-link">Home</a>
                <a href="#" class="nav-link">Trending</a>
                <a href="#" class="nav-link">Categories</a>
            </nav>
        </div>
    </header>

    <main class="container">
        <?php if ($post): ?>
            <article class="post-detail">
                <div class="post-header">
                    <span class="badge">
                        <?php echo htmlspecialchars($post['category']); ?>
                    </span>
                    <h1>
                        <?php echo htmlspecialchars($post['title']); ?>
                    </h1>
                    <div class="meta-info">
                        <span>
                            <?php echo htmlspecialchars($post['author']); ?>
                        </span>
                        <span> • </span>
                        <span>
                            <?php echo htmlspecialchars($post['date']); ?>
                        </span>
                    </div>
                </div>
                <div class="post-image-container">
                    <img src="<?php echo htmlspecialchars($post['image']); ?>"
                        alt="<?php echo htmlspecialchars($post['title']); ?>">
                </div>
                <div class="post-content">
                    <p class="summary">
                        <?php echo htmlspecialchars($post['summary']); ?>
                    </p>
                    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore
                        et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut
                        aliquip ex ea commodo consequat.</p>
                    <p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla
                        pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit
                        anim id est laborum.</p>
                </div>
                <div class="post-actions">
                    <!-- Adjusted path: go up one level to index.html -->
                    <a href="../index.html" class="btn-back">← Back to List</a>
                </div>
            </article>
        <?php else: ?>
            <div class="error-container" style="text-align: center; padding: 4rem 0;">
                <h2>Post Not Found</h2>
                <p>Sorry, the article you are looking for does not exist.</p>
                <a href="../index.html" class="btn-back">Go Back Home</a>
            </div>
        <?php endif; ?>
    </main>

    <footer class="main-footer">
        <div class="container">
            <p>&copy; 2024 InsightZine. All rights reserved.</p>
        </div>
    </footer>
</body>

</html>