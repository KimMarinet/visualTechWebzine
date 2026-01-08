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

    // Fetch all posts for the list at bottom
    $stmt = $pdo->prepare("SELECT * FROM board_webzine");
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

                <div class="post-content">
                    <p class="summary">
                        <?php echo htmlspecialchars($post['summary']); ?>
                    </p>
                    <div class="content-body">
                        <?php echo $post['content']; // Allow HTML content ?>
                    </div>
                </div>
                <div class="post-actions">
                    <div class="post-actions">
                        <a href="../../index.html" class="btn-back">Back to List</a>
                        <div class="admin-actions" style="margin-left: auto;">
                            <button id="btn-edit" class="btn-action btn-edit">Edit</button>
                            <button id="btn-delete" class="btn-action btn-delete">Delete</button>
                        </div>
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

            <style>
                .post-actions {
                    display: flex;
                    align-items: center;
                }

                .btn-action {
                    padding: 8px 16px;
                    border: none;
                    border-radius: 4px;
                    cursor: pointer;
                    font-weight: 500;
                    margin-left: 8px;
                }

                .btn-edit {
                    background-color: #f0f0f0;
                    color: #333;
                }

                .btn-delete {
                    background-color: #ff4d4f;
                    color: white;
                }

                .btn-delete:hover {
                    background-color: #ff7875;
                }

                /* Modal Styles */
                .modal-overlay {
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0, 0, 0, 0.5);
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    z-index: 1000;
                }

                .modal-content {
                    background: white;
                    padding: 24px;
                    border-radius: 8px;
                    width: 320px;
                    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                }

                .modal-content h3 {
                    margin-top: 0;
                    margin-bottom: 12px;
                }

                .form-group {
                    margin-bottom: 16px;
                }

                .form-group input {
                    width: 100%;
                    padding: 8px;
                    border: 1px solid #ddd;
                    border-radius: 4px;
                    box-sizing: border-box;
                }

                .modal-buttons {
                    display: flex;
                    justify-content: flex-end;
                    gap: 8px;
                }

                .btn-confirm {
                    background-color: #1890ff;
                    color: white;
                    padding: 8px 16px;
                    border: none;
                    border-radius: 4px;
                    cursor: pointer;
                }

                .btn-cancel {
                    background-color: #f5f5f5;
                    color: #666;
                    padding: 8px 16px;
                    border: none;
                    border-radius: 4px;
                    cursor: pointer;
                }
            </style>
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
                                    class="<?php echo ($p['seq'] === $id) ? 'current-post' : ''; ?>">
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