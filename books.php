<?php 
session_start();
include 'includes/db.php'; 
include 'includes/header_include.php'; 
?>

<style>
.container {
    flex: 1;
}
</style>
 
<?php
// Build query with followed users prioritization
$isLoggedIn = isset($_SESSION['user_id']);
$followedUsersCondition = '';

if ($isLoggedIn) {
    $currentUserId = $_SESSION['user_id'];
    // Get list of users that current user follows
    $followedUsersQuery = "SELECT following_id FROM follows WHERE follower_id = $currentUserId";
    $followedResult = mysqli_query($conn, $followedUsersQuery);

    if ($followedResult && mysqli_num_rows($followedResult) > 0) {
        $followedUserIds = [];
        while ($row = mysqli_fetch_assoc($followedResult)) {
            $followedUserIds[] = $row['following_id'];
        }
        $followedIdsString = implode(',', $followedUserIds);
        // Order by followed users first, then by creation date
        $followedUsersCondition = " ORDER BY CASE WHEN uploaded_by IN ($followedIdsString) THEN 0 ELSE 1 END, created_at DESC";
    } else {
        $followedUsersCondition = " ORDER BY created_at DESC";
    }
} else {
    $followedUsersCondition = " ORDER BY created_at DESC";
}
?>

<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); }
    
    .container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 40px 20px;
        flex: 1;
    }

    .books-header {
        margin-bottom: 50px;
        text-align: center;
    }

    .books-header h2 {
        font-size: 42px;
        font-weight: 700;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin-bottom: 12px;
    }

    .books-header p {
        font-size: 16px;
        color: #555;
    }

    .search-bar {
        max-width: 600px;
        margin: 0 auto 40px;
        position: relative;
    }

    .search-bar input {
        width: 100%;
        padding: 14px 20px;
        font-size: 15px;
        border: 2px solid #e0e7ff;
        border-radius: 12px;
        transition: all 0.3s;
        background: white;
    }

    .search-bar input:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .books-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 28px;
        margin-bottom: 40px;
    }

    .book-card {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    .book-card:hover {
        box-shadow: 0 12px 28px rgba(102, 126, 234, 0.2);
        transform: translateY(-8px);
    }

    .book-thumb-container {
        position: relative;
        width: 100%;
        padding-top: 100%; /* Square aspect ratio - shorter */
        background: linear-gradient(135deg, #f5f7fa 0%, #eff2f5 100%);
        overflow: hidden;
    }

    .book-thumb-container canvas {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .book-thumb-placeholder {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        font-size: 48px;
    }

    .book-category {
        position: absolute;
        top: 12px;
        right: 12px;
        background: rgba(102, 126, 234, 0.9);
        color: white;
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
        backdrop-filter: blur(8px);
    }

    .book-info {
        padding: 16px;
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .book-title {
        font-size: 16px;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 6px;
        line-height: 1.3;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .book-author-section {
        display: flex;
        align-items: center;
        gap: 6px;
        margin-bottom: 8px;
    }

    .book-author-label {
        font-size: 12px;
        color: #6b7280;
    }

    .book-author {
        font-size: 13px;
        color: #667eea;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.3s;
        display: inline-block;
    }

    .book-author:hover {
        color: #764ba2;
        text-decoration: underline;
    }

    .book-description {
        font-size: 12px;
        color: #6b7280;
        line-height: 1.4;
        margin-bottom: 8px;
        display: -webkit-box;
        -webkit-line-clamp: 1;
        -webkit-box-orient: vertical;
        overflow: hidden;
        flex: 1;
    }

    .book-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 11px;
        color: #9ca3af;
        margin-bottom: 12px;
        padding-top: 8px;
        border-top: 1px solid #f0f0f0;
    }

    .book-actions {
        display: flex;
        gap: 8px;
    }

    .btn {
        flex: 1;
        padding: 8px 12px;
        border: none;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        text-decoration: none;
        text-align: center;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        border: 2px solid transparent;
    }

    .btn-view {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
    }

    .btn-view:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(102, 126, 234, 0.4);
    }

    .btn-download {
        background: white;
        color: #667eea;
        border: 2px solid #e0e7ff;
    }

    .btn-download:hover {
        background: #f8f9ff;
        border-color: #667eea;
        box-shadow: 0 2px 8px rgba(102, 126, 234, 0.15);
    }

    .btn-disabled {
        background: #f3f4f6;
        color: #9ca3af;
        cursor: not-allowed;
        border: 2px solid #e5e7eb;
    }

    .btn-disabled:hover {
        background: #f3f4f6;
        box-shadow: none;
    }

    .empty-message {
        grid-column: 1 / -1;
        text-align: center;
        padding: 80px 40px;
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    }

    .empty-message-icon {
        font-size: 64px;
        margin-bottom: 20px;
    }

    .empty-message h3 {
        font-size: 24px;
        color: #1f2937;
        margin-bottom: 8px;
    }

    .empty-message p {
        font-size: 15px;
        color: #6b7280;
    }

    @media (max-width: 1024px) {
        .books-grid {
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 24px;
        }
    }

    @media (max-width: 640px) {
        .container {
            padding: 20px 12px;
        }

        .books-header h2 {
            font-size: 32px;
        }

        .books-grid {
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 16px;
        }

        .book-card {
            border-radius: 12px;
        }

        .book-info {
            padding: 16px;
        }

        .book-title {
            font-size: 16px;
        }

        .book-actions {
            gap: 8px;
        }

        .btn {
            padding: 8px 10px;
            font-size: 12px;
        }
    }

    .filter-section {
        display: flex;
        gap: 12px;
        justify-content: center;
        margin-bottom: 30px;
        flex-wrap: wrap;
    }

    .filter-btn {
        padding: 8px 16px;
        border: 2px solid #e0e7ff;
        background: white;
        color: #667eea;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
    }

    .filter-btn:hover,
    .filter-btn.active {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-color: transparent;
    }
</style>

<div class="container">
    <div class="books-header">
        <h2>📚 Library</h2>
        <p>Discover and explore our collection of professional resources</p>
    </div>

    <div class="search-bar">
        <input type="text" id="bookSearch" placeholder="Search books by title or author..." />
    </div>

    <?php
    $sql = "SELECT b.*, u.username, u.id as user_id FROM books b LEFT JOIN users u ON b.uploaded_by = u.id $followedUsersCondition";
    $result = mysqli_query($conn, $sql);

    if ($result !== false && mysqli_num_rows($result) > 0) {
        echo '<div class="books-grid">';

        while ($row = mysqli_fetch_assoc($result)) {
            $filename = !empty($row['file_path']) ? basename($row['file_path']) : '';
            $author_name = $row['author'] ?? 'Unknown';
            $uploader_name = $row['username'] ?? 'Anonymous';
            $user_id = $row['user_id'];
            
            echo '<div class="book-card" data-title="' . htmlspecialchars($row['title'] ?? '') . '" data-author="' . htmlspecialchars($author_name) . '">';

            // Book thumbnail
            echo '<div class="book-thumb-container">';
            if (!empty($row['file_path'])) {
                $pdfUrl = 'serve_pdf_new.php?file=uploads/books/' . rawurlencode($filename) . '&inline=1';
                echo '<canvas class="pdf-thumb-canvas" data-pdf="' . htmlspecialchars($pdfUrl, ENT_QUOTES) . '" data-w="280" data-h="280" style="width:100%;height:100%;"></canvas>';
            } else {
                echo '<div class="book-thumb-placeholder">📖</div>';
            }
            echo '<div class="book-category">' . htmlspecialchars($row['book_category'] ?? 'General') . '</div>';
            echo '</div>';

            // Book info
            echo '<div class="book-info">';
            echo '<div class="book-title">' . htmlspecialchars($row['title'] ?? '') . '</div>';
            echo '<div class="book-author-section">';
            echo '<span class="book-author-label">by</span>';
            if ($user_id) {
                echo '<a href="viewprofile.php?user_id=' . htmlspecialchars($user_id) . '" class="book-author">' . htmlspecialchars($uploader_name) . '</a>';
            } else {
                echo '<span class="book-author">' . htmlspecialchars($uploader_name) . '</span>';
            }
            echo '</div>';
            echo '<div class="book-description">' . htmlspecialchars($row['description'] ?? '') . '</div>';
            echo '<div class="book-meta">';
            echo '<span>' . (isset($row['created_at']) ? date('M d, Y', strtotime($row['created_at'])) : '') . '</span>';
            echo '</div>';
            
            // Action buttons
            echo '<div class="book-actions">';

            if (!empty($row['link'])) {
                echo '<a href="' . htmlspecialchars($row['link']) . '" target="_blank" class="btn btn-view" onclick="event.stopPropagation()">View</a>';
            } elseif (!empty($row['file_path'])) {
                echo '<a href="viewbooks.php?file=' . rawurlencode($filename) . '" class="btn btn-view" target="_blank" onclick="event.stopPropagation()">View</a>';
            } else {
                echo '<button class="btn btn-disabled" disabled>View</button>';
            }

            if (!empty($row['file_path'])) {
                echo '<a href="uploads/books/' . htmlspecialchars($filename) . '" download class="btn btn-download" onclick="event.stopPropagation()">Download</a>';
            } else {
                echo '<button class="btn btn-disabled" disabled>Download</button>';
            }

            echo '</div>';
            echo '</div>';
            echo '</div>';
        }

        echo '</div>';
    } else {
        echo '<div class="books-grid"><div class="empty-message"><div class="empty-message-icon">📚</div><h3>No Books Available</h3><p>Start by uploading your first book to the library.</p></div></div>';
    }
    ?>
</div>

<!-- ADVERTISEMENTS (bottom carousel) -->
<?php
$ad_position = 'books_bottom';
include 'display-ads.php';
?>

<?php include 'includes/footer.php'; ?>
<script src="assets/pdf-thumb.js"></script><script>
// Book search functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('bookSearch');
    const bookCards = document.querySelectorAll('.book-card');
    const booksGrid = document.querySelector('.books-grid');

    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            let visibleCount = 0;

            bookCards.forEach(card => {
                const title = card.dataset.title.toLowerCase();
                const author = card.dataset.author.toLowerCase();
                
                if (title.includes(searchTerm) || author.includes(searchTerm)) {
                    card.style.display = '';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            // Show "no results" message if nothing matches
            let emptyMsg = booksGrid.querySelector('.empty-message.search-result');
            if (visibleCount === 0 && searchTerm) {
                if (!emptyMsg) {
                    emptyMsg = document.createElement('div');
                    emptyMsg.className = 'empty-message search-result';
                    emptyMsg.style.gridColumn = '1 / -1';
                    emptyMsg.innerHTML = '<div class="empty-message-icon">🔍</div><h3>No Results Found</h3><p>Try searching with different keywords.</p>';
                    booksGrid.appendChild(emptyMsg);
                }
                emptyMsg.style.display = '';
            } else if (emptyMsg) {
                emptyMsg.style.display = 'none';
            }
        });
    }
});
</script>