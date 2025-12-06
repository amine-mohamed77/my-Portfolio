/**
 * Portfolio API Integration
 * Dynamically loads skills and projects from admin dashboard
 */

const API_BASE = './admin/api/';

// ============================================
// LOAD SKILLS FROM DATABASE
// ============================================

async function loadSkillsFromDB() {
    try {
        const response = await fetch(API_BASE + 'skills.php?active_only=true');
        const result = await response.json();
        
        if (result.success && result.data.grouped) {
            updateSkillsUI(result.data.grouped);
        }
    } catch (error) {
        console.error('Failed to load skills from database:', error);
        // Fallback to static skills if API fails
    }
}

function updateSkillsUI(groupedSkills) {
    // Find the skills grid container
    const skillsGrid = document.querySelector('#skills .grid');
    
    if (!skillsGrid) return;
    
    // Clear existing skills
    skillsGrid.innerHTML = '';
    
    // Iterate through categories
    for (const [category, skills] of Object.entries(groupedSkills)) {
        skills.forEach(skill => {
            const skillCard = createSkillCard(skill);
            skillsGrid.appendChild(skillCard);
        });
    }
}

function createSkillCard(skill) {
    const card = document.createElement('div');
    card.className = 'skill-card group';
    card.setAttribute('data-skill', skill.name.toLowerCase());
    
    card.innerHTML = `
        <div class="skill-card-inner">
            <div class="skill-card-front">
                <div class="skill-icon">
                    <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center font-bold text-white text-lg">
                        ${skill.icon_value || skill.name.substring(0, 2)}
                    </div>
                </div>
                <h3 class="skill-name">${escapeHtml(skill.name)}</h3>
                <div class="skill-level">${skill.level}%</div>
                <div class="skill-bar">
                    <div class="skill-progress" style="width: ${skill.level}%"></div>
                </div>
            </div>
        </div>
    `;
    
    return card;
}

// ============================================
// LOAD PROJECTS FROM DATABASE
// ============================================

async function loadProjectsFromDB() {
    try {
        const response = await fetch(API_BASE + 'projects.php?active_only=true');
        const result = await response.json();
        
        if (result.success && result.data.projects) {
            updateProjectsUI(result.data.projects);
        }
    } catch (error) {
        console.error('Failed to load projects from database:', error);
        // Fallback to static projects if API fails
    }
}

function updateProjectsUI(projects) {
    // Find the projects grid container
    const projectsGrid = document.querySelector('#projects .grid');
    
    if (!projectsGrid) return;
    
    // Clear existing projects
    projectsGrid.innerHTML = '';
    
    // Add projects
    projects.forEach(project => {
        const projectCard = createProjectCard(project);
        projectsGrid.appendChild(projectCard);
    });
}

function createProjectCard(project) {
    const card = document.createElement('div');
    card.className = 'relative glass rounded-xl overflow-hidden card-3d glow-hover group';
    
    // Create tech stack badges
    const techBadges = project.tech_stack.map(tech => {
        const color = getTechColor(tech);
        return `<span class="px-3 py-1 bg-${color}-500/20 text-${color}-300 rounded-full text-xs">${escapeHtml(tech)}</span>`;
    }).join('');
    
    card.innerHTML = `
        <canvas class="project-mini-canvas"></canvas>
        <div class="p-6">
            ${project.image_path ? `
                <div class="h-48 rounded-lg mb-6 overflow-hidden">
                    <img src="${project.image_path}" alt="${escapeHtml(project.title)}" class="w-full h-full object-cover">
                </div>
            ` : `
                <div class="h-48 gradient-primary rounded-lg mb-6 flex items-center justify-center">
                    <span class="text-4xl">🚀</span>
                </div>
            `}
            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">${escapeHtml(project.title)}</h3>
            <p class="text-gray-600 dark:text-white/70 mb-4">${escapeHtml(project.description)}</p>
            <div class="flex flex-wrap gap-2 mb-4">
                ${techBadges}
            </div>
            <div class="flex gap-3">
                ${project.live_url ? `
                    <a href="${project.live_url}" target="_blank" class="flex-1 bg-blue-500 hover:bg-blue-600 dark:bg-white/10 dark:hover:bg-white/20 text-white py-2 rounded-lg transition-all text-center">
                        Live Demo
                    </a>
                ` : ''}
                ${project.github_url ? `
                    <a href="${project.github_url}" target="_blank" class="flex-1 border border-gray-300 dark:border-white/20 hover:border-gray-400 dark:hover:border-white/40 text-gray-900 dark:text-white py-2 rounded-lg transition-all text-center">
                        GitHub
                    </a>
                ` : ''}
            </div>
        </div>
    `;
    
    return card;
}

// ============================================
// UTILITY FUNCTIONS
// ============================================

function getTechColor(tech) {
    const colorMap = {
        'laravel': 'red',
        'vue': 'green',
        'vue.js': 'green',
        'react': 'blue',
        'javascript': 'yellow',
        'php': 'purple',
        'mysql': 'blue',
        'postgresql': 'blue',
        'node': 'green',
        'node.js': 'green',
        'docker': 'cyan',
        'mongodb': 'green'
    };
    
    return colorMap[tech.toLowerCase()] || 'gray';
}

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

// ============================================
// INITIALIZE ON PAGE LOAD
// ============================================

// Check if we want to use dynamic data
const USE_DYNAMIC_DATA = false; // Set to true to enable dynamic loading

if (USE_DYNAMIC_DATA) {
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Loading dynamic data from database...');
        loadSkillsFromDB();
        loadProjectsFromDB();
    });
}

// Export functions for manual usage
window.portfolioAPI = {
    loadSkills: loadSkillsFromDB,
    loadProjects: loadProjectsFromDB,
    enableDynamic: function() {
        loadSkillsFromDB();
        loadProjectsFromDB();
    }
};
