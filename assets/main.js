
let searchTimeout;
function handleLiveSearch(query) {
    let resultsBox = document.getElementById('searchResults');
    clearTimeout(searchTimeout);
    
    if(query.length < 2) {
        resultsBox.style.display = 'none';
        return;
    }
    
    searchTimeout = setTimeout(() => {
        let fd = new FormData();
        fd.append('ajax_action', 'live_search');
        fd.append('query', query);
        
        fetch('dashboard.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            resultsBox.innerHTML = data.html;
            resultsBox.style.display = 'block';
        });
    }, 300); 
}

document.addEventListener('click', function(e) {
    let searchInput = document.getElementById('topSearchInput');
    let resultsBox = document.getElementById('searchResults');
    if(searchInput && resultsBox && !searchInput.contains(e.target)) {
        resultsBox.style.display = 'none';
    }
});


function showToast(message) {
    let toast = document.getElementById("toastMessage");
    document.getElementById("toastText").innerText = message;
    toast.className = "toast show";
    setTimeout(function(){ toast.className = toast.className.replace("show", ""); }, 3000);
}


function toggleLike(postId, btn) {
    let fd = new FormData(); fd.append('ajax_action', 'toggle_like'); fd.append('post_id', postId);
    fetch('dashboard.php', { method: 'POST', body: fd }).then(r=>r.json()).then(data => {
        if(data.status === 'success') {
            data.is_liked ? btn.classList.add('active') : btn.classList.remove('active');
            btn.style.color = data.is_liked ? 'var(--uiu-orange)' : 'var(--text-muted)';
            document.getElementById('like-count-'+postId).innerHTML = '<div style="background: var(--uiu-orange); border-radius: 50%; width: 18px; height: 18px; display: flex; align-items: center; justify-content: center;"><svg width="10" height="10" fill="white" viewBox="0 0 20 20"><path d="M2 10.5a1.5 1.5 0 113 0v6a1.5 1.5 0 01-3 0v-6zM6 10.333v5.43a2 2 0 001.106 1.79l.05.025A4 4 0 008.943 18h5.416a2 2 0 001.962-1.608l1.2-6A2 2 0 0015.56 8H12V4a2 2 0 00-2-2 1 1 0 00-1 1v.667a4 4 0 01-.8 2.4L6.8 7.933a4 4 0 00-.8 2.4z"></path></svg></div> ' + data.count + ' Likes';
        }
    });
}

function toggleSave(postId, btn) {
    let fd = new FormData(); fd.append('ajax_action', 'toggle_save'); fd.append('post_id', postId);
    fetch('dashboard.php', { method: 'POST', body: fd }).then(r=>r.json()).then(data => {
        if(data.status === 'success') { 
            data.saved ? btn.classList.add('active') : btn.classList.remove('active'); 
            btn.style.color = data.saved ? 'var(--uiu-orange)' : 'var(--text-muted)';
            showToast(data.message);
        }
    });
}


function toggleMenu(menuId) {
    let menu = document.getElementById(menuId);
    menu.style.display = (menu.style.display === 'block') ? 'none' : 'block';
}

function openDeleteModal(postId) {
    document.getElementById('post-menu-' + postId).style.display = 'none';
    let modal = document.getElementById('customDeleteModal');
    let confirmBtn = document.getElementById('confirmDeleteBtn');
    modal.style.display = 'flex';
    
    confirmBtn.onclick = function() {
        let fd = new FormData(); fd.append('ajax_action', 'delete_post'); fd.append('post_id', postId);
        fetch('dashboard.php', { method: 'POST', body: fd }).then(r=>r.json()).then(data => {
            if(data.status === 'success') {
                document.getElementById('post-card-'+postId).style.display = 'none';
                modal.style.display = 'none';
                showToast("Post deleted successfully.");
            }
        });
    };
}

function openEditPost(postId) {
    let currentText = document.getElementById('post-content-'+postId).getAttribute('data-raw');
    document.getElementById('editPostId').value = postId;
    document.getElementById('editText').value = currentText;
    document.getElementById('editModal').style.display = 'flex';
    document.getElementById('post-menu-'+postId).style.display = 'none';
}

function submitEdit() {
    let postId = document.getElementById('editPostId').value;
    let newContent = document.getElementById('editText').value;
    let fd = new FormData(); fd.append('ajax_action', 'edit_post'); fd.append('post_id', postId); fd.append('content', newContent);
    fetch('dashboard.php', { method: 'POST', body: fd }).then(r=>r.json()).then(data => {
        if(data.status === 'success') {
            let pEle = document.getElementById('post-content-'+postId);
            pEle.innerText = data.new_content;
            pEle.setAttribute('data-raw', newContent);
            document.getElementById('editModal').style.display = 'none';
            showToast("Post edited successfully.");
            setTimeout(() => location.reload(), 1500); 
        }
    });
}

function viewEditHistory(postId) {
    let fd = new FormData(); fd.append('ajax_action', 'get_edit_history'); fd.append('post_id', postId);
    fetch('dashboard.php', { method: 'POST', body: fd }).then(r=>r.json()).then(data => {
        if(data.status === 'success') {
            document.getElementById('historyContent').innerHTML = data.html;
            document.getElementById('historyModal').style.display = 'flex';
        }
    });
}

let pollOptionCount = 2;
function addPollOption() {
    if(pollOptionCount >= 10) { showToast("Maximum 10 options allowed."); return; }
    pollOptionCount++;
    let container = document.getElementById('pollOptionsContainer');
    let input = document.createElement('input');
    input.type = 'text';
    input.name = 'poll_options[]';
    input.className = 'poll-input';
    input.style = 'width: 100%; padding: 10px 12px; margin-bottom: 10px; border: 1px solid var(--border-light); border-radius: 8px; font-size: 14px; outline: none;';
    input.placeholder = 'Option ' + pollOptionCount;
    container.appendChild(input);
}

function submitVote(postId, optionIndex) {
    let fd = new FormData(); fd.append('ajax_action', 'vote_poll'); fd.append('post_id', postId); fd.append('option_index', optionIndex);
    fetch('dashboard.php', { method: 'POST', body: fd }).then(r=>r.json()).then(data => {
        if(data.status === 'success') {
            let wrapper = document.getElementById('poll-wrapper-' + postId);
            let rows = wrapper.querySelectorAll('.poll-option-row');
            rows.forEach((row, i) => {
                row.onclick = null; 
                let pct = data.results[i] !== undefined ? data.results[i] : 0;
                
                let prog = document.createElement('div');
                prog.className = 'poll-progress';
                prog.style.width = pct + '%';
                row.insertBefore(prog, row.firstChild);
                
                let circle = row.querySelector('.poll-content div');
                if(circle && circle.tagName === 'DIV') {
                    let span = document.createElement('span');
                    span.style = "font-size:13px; font-weight:600; color:var(--text-main);";
                    span.innerText = pct + '%';
                    circle.parentNode.replaceChild(span, circle);
                }
            });
            document.getElementById('poll-total-' + postId).innerText = data.total + ' votes';
            showToast("Vote recorded successfully!");
        }
    });
}
function previewMedia(input) {
    if (input.files && input.files[0]) {
        let file = input.files[0];
        document.getElementById('mediaPreview').style.display = 'block';
        if(file.type.startsWith('image/')) {
            let reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('imgPreview').src = e.target.result;
                document.getElementById('imgPreview').style.display = 'block';
                document.getElementById('docPreview').style.display = 'none';
            }
            reader.readAsDataURL(file);
        } else {
            document.getElementById('imgPreview').style.display = 'none';
            document.getElementById('docPreview').style.display = 'block';
        }
    }
}
function clearMedia() { document.getElementById('mediaUpload').value = ""; document.getElementById('mediaPreview').style.display = 'none'; }
function clearPoll() { document.getElementById('pollCreator').style.display = 'none'; document.querySelectorAll('.poll-input').forEach(i => i.value = ""); }


function openShareModal(postId) {
    document.getElementById('sharePostId').value = postId;
    document.getElementById('shareText').value = "";
    document.getElementById('shareModal').style.display = 'flex';
    document.getElementById('shareText').focus();
}

function submitShare() {
    let fd = new FormData(); fd.append('ajax_action', 'share_post'); fd.append('post_id', document.getElementById('sharePostId').value); fd.append('share_text', document.getElementById('shareText').value);
    fetch('dashboard.php', { method: 'POST', body: fd }).then(r=>r.json()).then(data => { 
        if(data.status === 'success') { 
            showToast("Post shared on your timeline!"); 
            setTimeout(() => location.reload(), 1000); 
        } 
    });
}

function toggleComments(postId) {
    let sec = document.getElementById('comments-' + postId);
    sec.style.display = (sec.style.display === 'block') ? 'none' : 'block';
    if (sec.style.display === 'block') document.getElementById('comment-input-' + postId).focus();
}

function toggleCommentLike(commentId, spanEle) {
    let fd = new FormData(); fd.append('ajax_action', 'toggle_comment_like'); fd.append('comment_id', commentId);
    fetch('dashboard.php', { method: 'POST', body: fd }).then(r=>r.json()).then(data => {
        if(data.status === 'success') { 
            spanEle.style.color = data.is_liked ? 'var(--uiu-orange)' : 'var(--text-muted)';
            let countSpan = document.getElementById('clike-count-' + commentId);
            if(countSpan) countSpan.innerText = (data.count > 0) ? `(${data.count})` : "";
        }
    });
}

function focusReply(postId, parentId, replyName) {
    let sec = document.getElementById('comments-' + postId);
    sec.style.display = 'block';
    let input = document.getElementById('comment-input-' + postId);
    input.focus();
    
    document.getElementById('reply-to-' + postId).value = parentId;
    let indicator = document.getElementById('replying-text-' + postId);
    document.getElementById('reply-name-' + postId).innerText = replyName;
    indicator.style.display = 'block';
    
    input.placeholder = "Write a reply...";
    input.style.borderColor = "var(--uiu-orange)";
}

function cancelReply(postId) {
    document.getElementById('reply-to-' + postId).value = "";
    document.getElementById('replying-text-' + postId).style.display = 'none';
    let input = document.getElementById('comment-input-' + postId);
    input.placeholder = "Write a comment... (Press Enter)";
    input.style.borderColor = "var(--border-light)";
}

function handleCommentSubmit(e, postId) {
    if (e.key === 'Enter') {
        e.preventDefault();
        let inputField = document.getElementById('comment-input-' + postId);
        let parentId = document.getElementById('reply-to-' + postId).value;
        let text = inputField.value.trim();
        if (text === '') return;

        let fd = new FormData(); 
        fd.append('ajax_action', 'add_comment'); 
        fd.append('post_id', postId); 
        fd.append('comment_text', text);
        if(parentId !== "") fd.append('parent_id', parentId);

        fetch('dashboard.php', { method: 'POST', body: fd }).then(r=>r.json()).then(data => {
            if (data.status === 'success') {
                if(data.resolved_parent_id === 'NULL') {
                    document.getElementById('comment-list-' + postId).insertAdjacentHTML('beforeend', data.html);
                } else {
                    let replyContainer = document.getElementById('replies-for-' + data.resolved_parent_id);
                    if(replyContainer) {
                        replyContainer.insertAdjacentHTML('beforeend', data.html);
                    }
                }
                inputField.value = ''; 
                cancelReply(postId);
                document.getElementById('comment-count-' + postId).innerText = data.total_comments + " Comments";
            }
        });
    }
}