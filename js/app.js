document.addEventListener('DOMContentLoaded', () => {
    const postGrid = document.getElementById('post-grid');
    const apiUrl = '/api/posts.php'; // Relative path if served from root

    // Fetch posts
    fetch(apiUrl)
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(posts => {
            renderPosts(posts);
        })
        .catch(error => {
            console.error('Error fetching posts:', error);
            postGrid.innerHTML = '<p class="error-message">게시물을 불러오는데 실패했습니다.</p>';
        });

    function renderPosts(posts) {
        postGrid.innerHTML = ''; // Clear loading state

        posts.forEach(post => {
            const card = document.createElement('article');
            card.className = 'card';

            card.innerHTML = `
                <a href="api/view.php?id=${post.id}" style="display: block; text-decoration: none; color: inherit;">
                    <div class="card-image-wrapper">
                        <img src="${post.image}" alt="${post.title}" class="card-image" loading="lazy">
                        <span class="badge" data-category="${post.category}">${post.category}</span>
                    </div>
                </a>
                <div class="card-content">
                    <a href="api/view.php?id=${post.id}" style="text-decoration: none; color: inherit;">
                        <h2 class="card-title">${post.title}</h2>
                    </a>
                    <p class="card-summary">${post.summary}</p>
                    <div class="card-footer">
                        <span class="author-avatar"></span>
                        <div class="footer-info">
                            <span class="author-name">${post.author}</span>
                            <span class="post-date">${post.date}</span>
                        </div>
                        <button class="icon-btn" aria-label="Share">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
                        </button>
                    </div>
                </div>
            `;
            postGrid.appendChild(card);
        });
    }
});
