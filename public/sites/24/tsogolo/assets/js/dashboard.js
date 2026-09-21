const STORAGE_KEY = 'tsogolo-hub-state-v1';
const DEFAULT_STATE = {
  role: 'user',
  products: [
    { id: 'p1', name: 'White Maize', price: 'MK 14,000', img: 'https://images.unsplash.com/photo-1551754655-cd27e38d2076?w=300' },
    { id: 'p2', name: 'Soya Beans', price: 'MK 22,000', img: 'https://images.unsplash.com/photo-1582284540020-8acbe03f4924?w=300' },
    { id: 'p3', name: 'Groundnuts', price: 'MK 16,500', img: 'https://images.unsplash.com/photo-1561395049-69684aacf8de?w=300' },
    { id: 'p4', name: 'Cassava', price: 'MK 9,500', img: 'https://images.unsplash.com/photo-1590779033100-9f60705a2f3b?w=300' }
  ],
  records: [
    { id: 'r1', item: 'Maize Seed', category: 'Exp', amount: '- MK 12k', user: 'John Mwale' },
    { id: 'r2', item: 'Soya Sale', category: 'Inc', amount: '+ MK 45k', user: 'John Mwale' },
    { id: 'r3', item: 'Fertilizer', category: 'Exp', amount: '- MK 60k', user: 'John Mwale' }
  ],
  jobs: [
    { id: 'j1', org: 'Save the Children', title: 'Data Entry Clerk', location: 'Lizulu Center', status: 'Open' },
    { id: 'j2', org: 'World Vision', title: 'Field Monitor', location: 'Ntcheu Boma', status: 'Open' }
  ],
  meetings: [
    { id: 'm1', title: 'VDC Planning: Education', time: 'Tomorrow 10am', location: 'Bwanje South', status: 'Meeting' }
  ],
  savedJobs: [],
  joinedMeetings: []
};

const state = loadState();

function loadState() {
  try {
    const raw = localStorage.getItem(STORAGE_KEY);
    if (!raw) return structuredClone(DEFAULT_STATE);
    const parsed = JSON.parse(raw);
    return { ...structuredClone(DEFAULT_STATE), ...parsed };
  } catch (error) {
    console.warn('Unable to load persisted state:', error);
    return structuredClone(DEFAULT_STATE);
  }
}

function saveState() {
  localStorage.setItem(STORAGE_KEY, JSON.stringify(state));
}

function isAdmin() {
  return state.role === 'admin';
}

function showAdminControls() {
  document.querySelectorAll('[data-admin-control]').forEach((el) => {
    el.style.display = isAdmin() ? 'block' : 'none';
  });
}

function setRole(role) {
  state.role = role;
  saveState();
  showAdminControls();
  document.getElementById('roleLabel').textContent = role === 'admin' ? 'Admin Access' : 'Member Access';
  document.getElementById('roleToggle').textContent = role === 'admin' ? 'Switch to User' : 'Switch to Admin';
  renderDynamicMarket();
  renderDynamicRecords();
  renderDynamicJobs();
  renderDynamicMeetings();
}

function renderDynamicMarket() {
  const grid = document.getElementById('marketGrid');
  grid.innerHTML = state.products.map((product) => `
    <div class="crop-card">
      <div class="crop-img" style="background-image: url('${product.img}')"></div>
      <div class="crop-body">
        <div style="font-weight:600; font-size:0.9rem;">${product.name}</div>
        <div class="crop-price">${product.price}</div>
        <div style="display:flex; gap:8px; flex-wrap:wrap;">
          <button class="btn-buy" style="flex:1; min-width:120px;">${translations[currentLang].btnCart}</button>
          ${isAdmin() ? `<button data-remove-product="${product.id}" class="btn-buy" style="flex:1; min-width:120px; background:#b6552c;">Remove</button>` : ''}
        </div>
      </div>
    </div>
  `).join('');
}

function renderDynamicRecords() {
  const tbody = document.getElementById('farmRecordsBody');
  tbody.innerHTML = state.records.map((record) => `
    <tr>
      <td>${record.item}</td>
      <td><span class="tag" style="background:${record.category === 'Inc' ? '#e8f5e9' : '#eee'}; color:${record.category === 'Inc' ? 'green' : 'inherit'}">${record.category}</span></td>
      <td style="color:${record.category === 'Inc' ? 'var(--teal)' : 'var(--rust)'}">${record.amount}</td>
      <td><button data-delete-record="${record.id}" class="btn-buy" style="width:auto; padding:6px 12px; background:#b6552c;">Remove</button></td>
    </tr>
  `).join('');

  const total = state.records.reduce((sum, record) => {
    const amount = Number(String(record.amount).replace(/[^0-9.-]/g, '')) || 0;
    return sum + amount;
  }, 0);

  document.getElementById('recordBalance').textContent = `Balance: MK ${Math.abs(total).toLocaleString()}`;
}

function renderDynamicJobs() {
  const list = document.getElementById('jobsList');
  list.innerHTML = state.jobs.map((job) => {
    const isSaved = state.savedJobs.includes(job.id);
    return `
      <div class="list-item">
        <div>
          <span class="tag tag-ngo">${job.org}</span><br>
          <strong>${job.title}</strong><br>
          <small>${job.location}</small>
        </div>
        <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
          <button data-apply-job="${job.id}" class="btn-buy" style="width:auto; padding:8px 20px; background:${isSaved ? '#0f7a6c' : '#132318'};">${isSaved ? 'Applied' : 'Apply'}</button>
          ${isAdmin() ? `<button data-remove-job="${job.id}" class="btn-buy" style="width:auto; padding:8px 20px; background:#b6552c;">Remove</button>` : ''}
        </div>
      </div>
    `;
  }).join('');
}

function renderDynamicMeetings() {
  const list = document.getElementById('govList');
  list.innerHTML = state.meetings.map((meeting) => {
    const joined = state.joinedMeetings.includes(meeting.id);
    return `
      <div class="list-item">
        <div>
          <span class="tag tag-gold">${meeting.status}</span><br>
          <strong>${meeting.title}</strong><br>
          <small>${meeting.location} · ${meeting.time}</small>
        </div>
        <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
          <button data-join-meeting="${meeting.id}" class="btn-buy" style="width:auto; padding:8px 20px; background:${joined ? '#0f7a6c' : '#132318'};">${joined ? 'Joined' : 'Join'}</button>
          ${isAdmin() ? `<button data-remove-meeting="${meeting.id}" class="btn-buy" style="width:auto; padding:8px 20px; background:#b6552c;">Remove</button>` : ''}
        </div>
      </div>
    `;
  }).join('');
}

function createId(prefix) {
  return `${prefix}-${Date.now()}-${Math.random().toString(16).slice(2, 8)}`;
}

function handleAddRecord(event) {
  event.preventDefault();
  const item = document.getElementById('recordItem').value.trim();
  const category = document.getElementById('recordCategory').value;
  const amount = document.getElementById('recordAmount').value.trim();
  if (!item || !amount) return;

  state.records.unshift({
    id: createId('r'),
    item,
    category,
    amount: `${category === 'Inc' ? '+' : '-'} MK ${amount}`,
    user: 'John Mwale'
  });

  saveState();
  renderRecords();
  event.target.reset();
}

function handleAddProduct(event) {
  event.preventDefault();
  if (!isAdmin()) return;
  const name = document.getElementById('productName').value.trim();
  const price = document.getElementById('productPrice').value.trim();
  const img = document.getElementById('productImage').value.trim() || 'https://images.unsplash.com/photo-1551754655-cd27e38d2076?w=300';

  if (!name || !price) return;

  state.products.unshift({ id: createId('p'), name, price: `MK ${price}`, img });
  saveState();
  renderMarket();
  event.target.reset();
}

function handleAddJob(event) {
  event.preventDefault();
  if (!isAdmin()) return;
  const org = document.getElementById('jobOrg').value.trim();
  const title = document.getElementById('jobTitle').value.trim();
  const location = document.getElementById('jobLocation').value.trim();
  if (!org || !title || !location) return;

  state.jobs.unshift({ id: createId('j'), org, title, location, status: 'Open' });
  saveState();
  renderJobs();
  event.target.reset();
}

function handleAddMeeting(event) {
  event.preventDefault();
  if (!isAdmin()) return;
  const title = document.getElementById('meetingTitle').value.trim();
  const location = document.getElementById('meetingLocation').value.trim();
  const time = document.getElementById('meetingTime').value.trim();
  if (!title || !location || !time) return;

  state.meetings.unshift({ id: createId('m'), title, location, time, status: 'Meeting' });
  saveState();
  renderMeetings();
  event.target.reset();
}

function handleRemoveProduct(id) {
  if (!isAdmin()) return;
  state.products = state.products.filter((product) => product.id !== id);
  saveState();
  renderMarket();
}

function handleRemoveRecord(id) {
  state.records = state.records.filter((record) => record.id !== id);
  saveState();
  renderRecords();
}

function handleRemoveJob(id) {
  if (!isAdmin()) return;
  state.jobs = state.jobs.filter((job) => job.id !== id);
  state.savedJobs = state.savedJobs.filter((itemId) => itemId !== id);
  saveState();
  renderJobs();
}

function handleRemoveMeeting(id) {
  if (!isAdmin()) return;
  state.meetings = state.meetings.filter((meeting) => meeting.id !== id);
  state.joinedMeetings = state.joinedMeetings.filter((itemId) => itemId !== id);
  saveState();
  renderMeetings();
}

function handleApplyJob(id) {
  if (state.savedJobs.includes(id)) {
    state.savedJobs = state.savedJobs.filter((itemId) => itemId !== id);
  } else {
    state.savedJobs.push(id);
  }
  saveState();
  renderJobs();
}

function handleJoinMeeting(id) {
  if (state.joinedMeetings.includes(id)) {
    state.joinedMeetings = state.joinedMeetings.filter((itemId) => itemId !== id);
  } else {
    state.joinedMeetings.push(id);
  }
  saveState();
  renderMeetings();
}

function initDashboard() {
  const roleToggle = document.getElementById('roleToggle');
  if (roleToggle) {
    roleToggle.addEventListener('click', () => {
      setRole(isAdmin() ? 'user' : 'admin');
    });
  }

  const recordForm = document.getElementById('farmRecordForm');
  if (recordForm) recordForm.addEventListener('submit', handleAddRecord);

  const productForm = document.getElementById('productForm');
  if (productForm) productForm.addEventListener('submit', handleAddProduct);

  const jobForm = document.getElementById('jobForm');
  if (jobForm) jobForm.addEventListener('submit', handleAddJob);

  const meetingForm = document.getElementById('meetingForm');
  if (meetingForm) meetingForm.addEventListener('submit', handleAddMeeting);

  document.addEventListener('click', (event) => {
    const removeProductId = event.target.getAttribute('data-remove-product');
    if (removeProductId) handleRemoveProduct(removeProductId);

    const removeRecordId = event.target.getAttribute('data-delete-record');
    if (removeRecordId) handleRemoveRecord(removeRecordId);

    const removeJobId = event.target.getAttribute('data-remove-job');
    if (removeJobId) handleRemoveJob(removeJobId);

    const removeMeetingId = event.target.getAttribute('data-remove-meeting');
    if (removeMeetingId) handleRemoveMeeting(removeMeetingId);

    const applyJobId = event.target.getAttribute('data-apply-job');
    if (applyJobId) handleApplyJob(applyJobId);

    const joinMeetingId = event.target.getAttribute('data-join-meeting');
    if (joinMeetingId) handleJoinMeeting(joinMeetingId);
  });

  showAdminControls();
  setRole(state.role);
  renderDynamicMarket();
  renderDynamicRecords();
  renderDynamicJobs();
  renderDynamicMeetings();
}

window.addEventListener('DOMContentLoaded', initDashboard);
