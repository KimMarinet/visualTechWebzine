<?php
// Include database connection
require_once '../db_connect.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$post = null;
$posts = [];

try {
    // Fetch specific post
    $stmt = $pdo->prepare("SELECT * FROM board_webzine WHERE seq = :id");
    $stmt->execute(['id' => $id]);
    $post = $stmt->fetch();

    // Pagination Setup
    $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    if ($page < 1)
        $page = 1;
    $limit = 5;
    $offset = ($page - 1) * $limit;

    // Get Total Count
    $countStmt = $pdo->prepare("SELECT count(*) FROM board_webzine");
    $countStmt->execute();
    $totalPosts = $countStmt->fetchColumn();
    $totalPages = ceil($totalPosts / $limit);

    // Fetch posts for the list at bottom with LIMIT
    $stmt = $pdo->prepare("SELECT * FROM board_webzine ORDER BY seq DESC LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
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
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Lora:ital,wght@0,400;0,500;0,600;1,400&display=swap"
        rel="stylesheet">
    <script src="../../js/board/view/view.js"></script>
</head>

<body>
    <header class="main-header">
        <div class="container header-container">
            <h1 class="logo">VisuaL<span class="highlight">Tech</span></h1>
            <nav class="main-nav">
                <!-- Adjusted path: go up one level to index.html -->
                <a href="../../index.html" class="nav-link">Home</a>
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

                <div class="admin-actions">
                    <button id="btn-edit" class="btn-action">Edit</button>
                    <span style="color: #ced4da; font-size: 0.3rem;">|</span>
                    <button id="btn-delete" class="btn-action">Delete</button>
                </div>

                <p class="summary">
                    <?php echo htmlspecialchars($post['summary']); ?>
                </p>

                <div class="post-content">
                    <div class="content-body">
                        <?php echo $post['content']; // Allow HTML content ?>
                    </div>
                    <a href="../../index.html" class="btn-back">Back to List</a>
                </div>
            </article>

            <!-- Admin Auth Modal -->
            <div id="admin-modal" class="modal-overlay" style="display: none;">
                <div class="modal-content">
                    <h3>Admin Authentication</h3>
                    <p>Please enter admin credentials to proceed.</p>
                    <form id="admin-form">
                        <div class="form-group">
                            <input type="text" id="admin-id" placeholder="Admin ID" required>
                        </div>
                        <div class="form-group">
                            <input type="password" id="admin-pw" placeholder="Password" required>
                        </div>
                        <div class="modal-buttons">
                            <button type="submit" class="btn-confirm">Confirm</button>
                            <button type="button" id="btn-close-modal" class="btn-cancel">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
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
                                <tr data-id="<?php echo $p['seq']; ?>"
                                    onclick="if(!event.target.closest('a')) location.href='?id=<?php echo $p['seq']; ?>&page=<?php echo $page; ?>';"
                                    style="cursor: pointer;" class="<?php echo ($p['seq'] === $id) ? 'current-post' : ''; ?>">
                                    <td><?php echo htmlspecialchars($p['title']); ?></td>
                                    <td><?php echo htmlspecialchars($p['author']); ?></td>
                                    <td><?php echo htmlspecialchars($p['date']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <div class="pagination">
                            <?php
                            $currentBlock = ceil($page / 10);
                            $startPage = ($currentBlock - 1) * 10 + 1;
                            $endPage = min($startPage + 9, $totalPages);
                            ?>

                            <!-- Jump -5 Pages (<<) -->
                            <?php if ($page > 5): ?>
                                <a href="?id=<?php echo $id; ?>&page=<?php echo max(1, $page - 5); ?>" class="page-link">&laquo;</a>
                            <?php endif; ?>

                            <!-- Prev Page (<) -->
                            <?php if ($page > 1): ?>
                                <a href="?id=<?php echo $id; ?>&page=<?php echo $page - 1; ?>" class="page-link">&lt;</a>
                            <?php endif; ?>

                            <!-- Page Numbers -->
                            <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                                <a href="?id=<?php echo $id; ?>&page=<?php echo $i; ?>"
                                    class="page-link <?php echo ($i == $page) ? 'active' : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>

                            <!-- Next Page (>) -->
                            <?php if ($page < $totalPages): ?>
                                <a href="?id=<?php echo $id; ?>&page=<?php echo $page + 1; ?>" class="page-link">&gt;</a>
                            <?php endif; ?>

                            <!-- Jump +5 Pages (>>) -->
                            <?php if ($page + 5 <= $totalPages): ?>
                                <a href="?id=<?php echo $id; ?>&page=<?php echo min($totalPages, $page + 5); ?>"
                                    class="page-link">&raquo;</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <style>
                    /* Pagination Styles */
                    /* Pagination Styles - Compact (0.5x) */
                    .pagination {
                        display: flex;
                        justify-content: center;
                        margin-top: 10px;
                        /* Reduced from 20px */
                        gap: 3px;
                        /* Reduced from 5px */
                    }

                    .page-link {
                        padding: 3px 6px;
                        /* Reduced from 6px 12px */
                        border: 1px solid #ddd;
                        color: #333;
                        text-decoration: none;
                        border-radius: 4px;
                        font-size: 0.75rem;
                        /* Reduced from 0.9rem */
                    }

                    .page-link:hover {
                        background-color: #f5f5f5;
                    }

                    .page-link.active {
                        background-color: var(--primary-color, #1a73e8);
                        color: white;
                        border-color: var(--primary-color, #1a73e8);
                    }

                    /* Compact Table Styles */
                    .post-list-table th,
                    .post-list-table td {
                        padding: 6px 8px !important;
                        /* Force reduced padding */
                        font-size: 0.85rem !important;
                        /* Smaller text */
                        line-height: 1.2;
                    }

                    .post-list-container {
                        transform-origin: top center;
                    }

                    /* Ensure table row hover effect works for clickability hint */
                    .post-list-table tbody tr:hover {
                        background-color: #f9f9f9;
                    }

                    .current-post {
                        background-color: #e3f2fd;
                        font-weight: 600;
                    }
                </style>
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