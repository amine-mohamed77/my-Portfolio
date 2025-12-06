/**
 * Admin Dashboard JavaScript
 * Handles CRUD operations, modals, and UI interactions
 * Version: 2.0 - Color Support Added
 */

console.log('Dashboard.js loaded - Version 2.0 with color support');

// API Base URL
const API_BASE = 'api/';

// Current editing IDs
let currentSkillId = null;
let currentProjectId = null;

// ============================================
// INITIALIZATION
// ============================================

document.addEventListener('DOMContentLoaded', function() {
    // Load initial data
    loadSkills();
    loadProjects();
    
    // Setup event listeners
    setupEventListeners();
    
    // Initialize theme
    initTheme();
});

// ============================================
// THEME MANAGEMENT
// ============================================

function initTheme() {
    const theme = localStorage.getItem('admin_theme') || 'light';
    if (theme === 'dark') {
        document.documentElement.classList.add('dark');
        document.getElementById('theme-icon-dark').classList.add('hidden');
        document.getElementById('theme-icon-light').classList.remove('hidden');
    }
}

function toggleTheme() {
    const html = document.documentElement;
    const isDark = html.classList.contains('dark');
    
    if (isDark) {
        html.classList.remove('dark');
        localStorage.setItem('admin_theme', 'light');
        document.getElementById('theme-icon-dark').classList.remove('hidden');
        document.getElementById('theme-icon-light').classList.add('hidden');
    } else {
        html.classList.add('dark');
        localStorage.setItem('admin_theme', 'dark');
        document.getElementById('theme-icon-dark').classList.add('hidden');
        document.getElementById('theme-icon-light').classList.remove('hidden');
    }
}

// ============================================
// EVENT LISTENERS
// ============================================

function setupEventListeners() {
    // Theme toggle
    document.getElementById('theme-toggle').addEventListener('click', toggleTheme);
    
    // Skill form now uses PHP POST - no JavaScript handler needed
    // document.getElementById('skill-form').addEventListener('submit', handleSkillSubmit);
    
    // Project form submit
    document.getElementById('project-form').addEventListener('submit', handleProjectSubmit);
    
    // Close modals on outside click
    document.getElementById('skill-modal').addEventListener('click', function(e) {
        if (e.target === this) closeSkillModal();
    });
    
    document.getElementById('project-modal').addEventListener('click', function(e) {
        if (e.target === this) closeProjectModal();
    });
}

// ============================================
// TAB MANAGEMENT
// ============================================

function switchTab(tab) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
    document.querySelectorAll('.tab-button').forEach(el => {
        el.classList.remove('active', 'border-blue-600', 'text-blue-600', 'dark:text-blue-400');
        el.classList.add('border-transparent', 'text-gray-600', 'dark:text-gray-400');
    });
    
    // Show selected tab
    document.getElementById('content-' + tab).classList.remove('hidden');
    const tabButton = document.getElementById('tab-' + tab);
    tabButton.classList.add('active', 'border-blue-600', 'text-blue-600', 'dark:text-blue-400');
    tabButton.classList.remove('border-transparent', 'text-gray-600', 'dark:text-gray-400');
}

// ============================================
// SKILLS MANAGEMENT
// ============================================

async function loadSkills() {
    try {
        // Add cache busting to prevent browser from caching API response
        const response = await fetch(API_BASE + 'skills.php?_=' + Date.now());
        const result = await response.json();
        
        if (result.success) {
            displaySkills(result.data.skills);
        } else {
            showError('Failed to load skills');
        }
    } catch (error) {
        console.error('Error loading skills:', error);
        showError('Failed to load skills');
    }
}

function displaySkills(skills) {
    const tbody = document.getElementById('skills-table-body');
    
    if (skills.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">No skills found</td></tr>';
        return;
    }
    
    tbody.innerHTML = skills.map(skill => {
        // Check if icon is URL, Material Icon, or text
        const isUrl = skill.icon_value && skill.icon_value.startsWith('http');
        const iconDisplay = isUrl 
            ? `<img src="${escapeHtml(skill.icon_value)}" alt="${escapeHtml(skill.name)}" class="w-8 h-8 object-contain">`
            : `<span class="text-sm font-bold">${escapeHtml(skill.icon_value || skill.name.substring(0, 2))}</span>`;
        
        return `
        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10 bg-gray-200 dark:bg-gray-600 rounded-lg flex items-center justify-center">
                        ${iconDisplay}
                    </div>
                    <div class="ml-4">
                        <div class="text-sm font-medium text-gray-900 dark:text-white">${escapeHtml(skill.name)}</div>
                    </div>
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                    <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2 mr-2 max-w-[100px]">
                        <div class="bg-blue-600 h-2 rounded-full" style="width: ${skill.level}%"></div>
                    </div>
                    <span class="text-sm text-gray-900 dark:text-white">${skill.level}%</span>
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200">
                    ${skill.category}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${skill.is_active ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' : 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200'}">
                    ${skill.is_active ? 'Active' : 'Inactive'}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                <a href="?edit_skill=${skill.id}" class="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300">Edit</a>
                <button onclick="deleteSkill(${skill.id})" class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300">Delete</button>
            </td>
        </tr>
    `;
    }).join('');
}

function openSkillModal(skillId = null) {
    currentSkillId = skillId;
    
    if (skillId) {
        // Edit mode - for PHP form, redirect with edit parameter
        window.location.href = '?edit_skill=' + skillId;
    } else {
        // Add mode - clear form and show modal
        document.getElementById('skill-form').reset();
        document.getElementById('skill-id').value = '';
        const colorPicker = document.getElementById('skill-color');
        const colorHex = document.getElementById('skill-color-hex');
        if (colorPicker) colorPicker.value = '#3b82f6';
        if (colorHex) colorHex.value = '#3b82f6';
        document.getElementById('skill-modal-title').textContent = '➕ Add New Skill';
        document.getElementById('skill-modal').classList.remove('hidden');
    }
}

function closeSkillModal() {
    document.getElementById('skill-modal').classList.add('hidden');
    currentSkillId = null;
}

async function loadSkillData(id) {
    try {
        console.log('Loading skill data for ID:', id);
        // Add cache busting to prevent browser from caching
        const response = await fetch(API_BASE + `skills.php?id=${id}&_=` + Date.now());
        const result = await response.json();
        
        console.log('Skill data received:', result);
        
        if (result.success) {
            const skill = result.data;
            document.getElementById('skill-id').value = skill.id;
            document.getElementById('skill-name').value = skill.name;
            document.getElementById('skill-level').value = skill.level;
            document.getElementById('skill-category').value = skill.category;
            document.getElementById('skill-icon').value = skill.icon_value || '';
            
            // Load color
            const color = skill.color || '#3b82f6';
            console.log('Setting color fields to:', color);
            
            const colorPicker = document.getElementById('skill-color');
            const colorHex = document.getElementById('skill-color-hex');
            
            if (colorPicker) {
                colorPicker.value = color;
                console.log('Color picker set to:', colorPicker.value);
            } else {
                console.error('❌ Color picker element not found!');
            }
            
            if (colorHex) {
                colorHex.value = color;
                console.log('Color hex input set to:', colorHex.value);
            } else {
                console.error('❌ Color hex input element not found!');
            }
        }
    } catch (error) {
        console.error('Error loading skill:', error);
        showError('Failed to load skill data');
    }
}

async function handleSkillSubmit(e) {
    e.preventDefault();
    
    console.log('🚀 FORM SUBMIT STARTED');
    
    const id = document.getElementById('skill-id').value;
    const colorInput = document.getElementById('skill-color');
    
    console.log('🎨 Color input element:', colorInput);
    console.log('🎨 Color input value:', colorInput ? colorInput.value : 'NOT FOUND');
    
    const colorValue = colorInput ? colorInput.value : '#3b82f6';
    
    const data = {
        name: document.getElementById('skill-name').value,
        level: parseInt(document.getElementById('skill-level').value),
        category: document.getElementById('skill-category').value,
        icon_type: 'text',
        icon_value: document.getElementById('skill-icon').value,
        color: colorValue,
        is_active: 1
    };
    
    console.log('💾 SAVING SKILL WITH COLOR:', colorValue);
    console.log('📦 FULL DATA BEING SENT:', JSON.stringify(data, null, 2));
    
    try {
        let response;
        
        if (id) {
            // Update existing skill
            data.id = parseInt(id);
            response = await fetch(API_BASE + 'skills.php', {
                method: 'PUT',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(data)
            });
        } else {
            // Create new skill
            response = await fetch(API_BASE + 'skills.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(data)
            });
        }
        
        const result = await response.json();
        console.log('✅ SERVER RESPONSE:', JSON.stringify(result, null, 2));
        
        if (result.success) {
            console.log('✅ SUCCESS! Color saved:', colorValue);
            showSuccess(result.message);
            closeSkillModal();
            loadSkills();
        } else {
            console.error('❌ SAVE FAILED:', result.message);
            showError(result.message);
        }
    } catch (error) {
        console.error('Error saving skill:', error);
        showError('Failed to save skill');
    }
}

async function editSkill(id) {
    openSkillModal(id);
}

async function deleteSkill(id) {
    if (!confirm('Are you sure you want to delete this skill?')) {
        return;
    }
    
    try {
        const response = await fetch(API_BASE + `skills.php?id=${id}`, {
            method: 'DELETE'
        });
        
        const result = await response.json();
        
        if (result.success) {
            showSuccess(result.message);
            loadSkills();
        } else {
            showError(result.message);
        }
    } catch (error) {
        console.error('Error deleting skill:', error);
        showError('Failed to delete skill');
    }
}

// ============================================
// PROJECTS MANAGEMENT
// ============================================

async function loadProjects() {
    try {
        const response = await fetch(API_BASE + 'projects.php');
        const result = await response.json();
        
        if (result.success) {
            displayProjects(result.data.projects);
        } else {
            showError('Failed to load projects');
        }
    } catch (error) {
        console.error('Error loading projects:', error);
        showError('Failed to load projects');
    }
}

function displayProjects(projects) {
    const tbody = document.getElementById('projects-table-body');
    
    if (projects.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">No projects found</td></tr>';
        return;
    }
    
    tbody.innerHTML = projects.map(project => `
        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
            <td class="px-6 py-4">
                <div class="text-sm font-medium text-gray-900 dark:text-white">${escapeHtml(project.title)}</div>
                <div class="text-sm text-gray-500 dark:text-gray-400">${escapeHtml(project.description.substring(0, 60))}...</div>
            </td>
            <td class="px-6 py-4">
                <div class="flex flex-wrap gap-1">
                    ${project.tech_stack.map(tech => `
                        <span class="px-2 py-1 text-xs bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded">${tech}</span>
                    `).join('')}
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                ${project.is_featured ? '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200">Featured</span>' : ''}
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${project.is_active ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' : 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200'}">
                    ${project.is_active ? 'Active' : 'Inactive'}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                <button onclick="editProject(${project.id})" class="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300">Edit</button>
                <button onclick="deleteProject(${project.id})" class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300">Delete</button>
            </td>
        </tr>
    `).join('');
}

function openProjectModal(projectId = null) {
    currentProjectId = projectId;
    
    if (projectId) {
        // Edit mode - load project data
        loadProjectData(projectId);
        document.getElementById('project-modal-title').textContent = 'Edit Project';
    } else {
        // Add mode - clear form
        document.getElementById('project-form').reset();
        document.getElementById('project-id').value = '';
        document.getElementById('project-modal-title').textContent = 'Add New Project';
    }
    
    document.getElementById('project-modal').classList.remove('hidden');
}

function closeProjectModal() {
    document.getElementById('project-modal').classList.add('hidden');
    currentProjectId = null;
}

async function loadProjectData(id) {
    try {
        const response = await fetch(API_BASE + `projects.php?id=${id}`);
        const result = await response.json();
        
        if (result.success) {
            const project = result.data;
            document.getElementById('project-id').value = project.id;
            document.getElementById('project-title').value = project.title;
            document.getElementById('project-description').value = project.description;
            document.getElementById('project-tech').value = project.tech_stack.join(', ');
            document.getElementById('project-live').value = project.live_url || '';
            document.getElementById('project-github').value = project.github_url || '';
            document.getElementById('project-featured').checked = project.is_featured == 1;
        }
    } catch (error) {
        console.error('Error loading project:', error);
        showError('Failed to load project data');
    }
}

async function handleProjectSubmit(e) {
    e.preventDefault();
    
    const id = document.getElementById('project-id').value;
    const formData = new FormData();
    
    formData.append('title', document.getElementById('project-title').value);
    formData.append('description', document.getElementById('project-description').value);
    formData.append('tech_stack', document.getElementById('project-tech').value);
    formData.append('live_url', document.getElementById('project-live').value);
    formData.append('github_url', document.getElementById('project-github').value);
    formData.append('is_featured', document.getElementById('project-featured').checked ? 1 : 0);
    formData.append('is_active', 1);
    
    const imageFile = document.getElementById('project-image').files[0];
    if (imageFile) {
        formData.append('image', imageFile);
    }
    
    if (id) {
        formData.append('id', id);
    }
    
    try {
        const response = await fetch(API_BASE + 'projects.php', {
            method: id ? 'POST' : 'POST', // Use POST for both create and update with FormData
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showSuccess(result.message);
            closeProjectModal();
            loadProjects();
        } else {
            showError(result.message);
        }
    } catch (error) {
        console.error('Error saving project:', error);
        showError('Failed to save project');
    }
}

async function editProject(id) {
    openProjectModal(id);
}

async function deleteProject(id) {
    if (!confirm('Are you sure you want to delete this project?')) {
        return;
    }
    
    try {
        const response = await fetch(API_BASE + `projects.php?id=${id}`, {
            method: 'DELETE'
        });
        
        const result = await response.json();
        
        if (result.success) {
            showSuccess(result.message);
            loadProjects();
        } else {
            showError(result.message);
        }
    } catch (error) {
        console.error('Error deleting project:', error);
        showError('Failed to delete project');
    }
}

// ============================================
// UTILITY FUNCTIONS
// ============================================

function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}

function showSuccess(message) {
    // You can implement a toast notification here
    alert(message);
}

function showError(message) {
    // You can implement a toast notification here
    alert('Error: ' + message);
}
