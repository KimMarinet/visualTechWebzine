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
                        <img src="${post.image || 'images/default-thumbnail.png'}" alt="${post.title}" class="card-image" loading="lazy">
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

            // Share Button Logic
            const shareBtn = card.querySelector('.icon-btn');
            shareBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation(); // Prevent triggering card click if any

                // 배포 시 URL 주소 바꾸기
                const shareUrl = new URL(`api/view.php?id=${post.id}`, window.location.href).href;

                navigator.clipboard.writeText(shareUrl).then(() => {
                    alert('주소가 복사되었습니다.\n' + shareUrl);
                }).catch(err => {
                    console.error('URL copy failed:', err);
                    alert('주소 복사에 실패했습니다.');
                });
            });

            postGrid.appendChild(card);
        });
    }

    // View Toggle Logic
    const btnGrid = document.getElementById('btn-grid');
    const btnList = document.getElementById('btn-list');

    // Load saved preference
    const savedView = localStorage.getItem('viewMode');
    if (savedView === 'list') {
        enableListView();
    }

    btnGrid.addEventListener('click', () => {
        postGrid.classList.remove('list-layout');
        btnGrid.classList.add('active');
        btnList.classList.remove('active');
        localStorage.setItem('viewMode', 'grid');
    });

    btnList.addEventListener('click', enableListView);

    function enableListView() {
        postGrid.classList.add('list-layout');
        btnList.classList.add('active');
        btnGrid.classList.remove('active');
        localStorage.setItem('viewMode', 'list');
    }
});
