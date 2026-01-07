<?php
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
// Include database connection
require_once '../db_connect.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$post = null;
$posts = [];

try {
    // Fetch specific post
    $stmt = $pdo->prepare("SELECT * FROM posts WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $post = $stmt->fetch();

    // Fetch all posts for the list at bottom
    $stmt = $pdo->prepare("SELECT * FROM posts");
    $stmt->execute();
    $posts = $stmt->fetchAll();
} catch (PDOException $e) {
    // Error handling can be added here
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
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/post-list.css">
    <link rel="stylesheet" href="../../css/board/view/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="../../js/board/view/view.js"></script>
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
                <?php if (!empty($post['image_url'])): ?>
                    <div class="post-image-container">
                        <img src="../../uploads/<?php echo htmlspecialchars($post['image_url']); ?>"
                            alt="<?php echo htmlspecialchars($post['title']); ?>">
                    </div>
                <?php endif; ?>
                <div class="post-content">
                    <p class="summary">
                        <?php echo htmlspecialchars($post['summary']); ?>
                    </p>
                    <p>
                        <?php echo htmlspecialchars($post['content']); ?>
                    </p>
                </div>
                <div class="post-actions">
                    <!-- Adjusted path: go up one level to index.html -->
                    <a href="../../index.html" class="btn-back">Back to List</a>
                </div>
            </article>
            <section class="post-list-container">
                <div class="table-responsive">
                    <table class="post-list-table">
                        <thead>
                            <tr>
                                <th width="60%">Title</th>
                                <th width="20%">Author</th>
                                <th width="20%">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($posts as $p): ?>
                                <tr data-id="<?php echo $p['id']; ?>"
                                    class="<?php echo ($p['id'] === $id) ? 'current-post' : ''; ?>">
                                    <td><?php echo htmlspecialchars($p['title']); ?></td>
                                    <td><?php echo htmlspecialchars($p['author']); ?></td>
                                    <td><?php echo htmlspecialchars($p['date']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
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