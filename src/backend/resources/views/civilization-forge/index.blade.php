<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Civilization Forge - Material Generation Engine</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<body class="bg-gray-900 text-gray-100" x-data="civilizationForge()">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-gray-800 border-b border-gray-700">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-orange-500 rounded-lg flex items-center justify-center">
                            <span class="text-white font-bold">🔥</span>
                        </div>
                        <h1 class="text-2xl font-bold text-orange-400">Civilization Forge</h1>
                    </div>
                    <div class="text-sm text-gray-400">Material Generation Engine</div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Configuration Panel -->
                <div class="lg:col-span-1">
                    <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
                        <h2 class="text-xl font-semibold mb-6 text-orange-400">Story Configuration</h2>
                        
                        <!-- Preset Selection -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium mb-2">World Preset</label>
                            <select x-model="config.preset" 
                                    @change="updatePresetInfo()"
                                    class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-white">
                                <option value="stable">Stable World</option>
                                <option value="faith">Faith World</option>
                                <option value="rational">Rational World</option>
                                <option value="political">Political World</option>
                                <option value="resource">Resource World</option>
                                <option value="chaotic">Chaotic World</option>
                            </select>
                            <div x-show="presetInfo.description" class="mt-2 text-sm text-gray-400">
                                <p x-text="presetInfo.description"></p>
                            </div>
                        </div>
                        
                        <!-- Structural Anchor -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium mb-2">Structural Anchor</label>
                            <select x-model="config.structuralAnchor" 
                                    @change="updateAnchorInfo()"
                                    class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-white">
                                <option value="academic_system">Academic System</option>
                                <option value="faction_system">Faction System</option>
                                <option value="commercial_system">Commercial System</option>
                            </select>
                            <div x-show="anchorInfo.description" class="mt-2 text-sm text-gray-400">
                                <p x-text="anchorInfo.description"></p>
                                <div class="mt-1">
                                    <span class="text-xs text-gray-500">Examples:</span>
                                    <ul class="text-xs text-gray-500 ml-4">
                                        <template x-for="example in anchorInfo.examples">
                                            <li x-text="example"></li>
                                        </template>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Author Intent Options -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-medium text-gray-300">Author Intent</h3>
                            
                            <div>
                                <label class="block text-sm font-medium mb-1">Narrative Density</label>
                                <select x-model="config.authorIntent.narrative_density" 
                                        class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-white">
                                    <option value="low">Sparse materials, focused story</option>
                                    <option value="medium">Balanced materials</option>
                                    <option value="high">Rich materials, complex story</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1">Power Progression</label>
                                <select x-model="config.authorIntent.power_gradient" 
                                        class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-white">
                                    <option value="gentle">Slow, gradual power changes</option>
                                    <option value="medium">Moderate power progression</option>
                                    <option value="steep">Rapid power escalation</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1">Resource Availability</label>
                                <select x-model="config.authorIntent.resource_density" 
                                        class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-white">
                                    <option value="scarce">Limited resources create conflict</option>
                                    <option value="medium">Balanced resource distribution</option>
                                    <option value="abundant">Plenty resources, different conflicts</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1">Belief System Complexity</label>
                                <select x-model="config.authorIntent.perception_complexity" 
                                        class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-white">
                                    <option value="simple">Uniform belief system</option>
                                    <option value="medium">Some ideological diversity</option>
                                    <option value="complex">Multiple competing belief systems</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1">Conflict Intensity</label>
                                <select x-model="config.authorIntent.conflict_intensity" 
                                        class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-white">
                                    <option value="low">Subtle, personal conflicts</option>
                                    <option value="medium">Balanced conflict levels</option>
                                    <option value="high">Intense, dramatic conflicts</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1">Social Detail Level</label>
                                <select x-model="config.authorIntent.social_thickness" 
                                        class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-white">
                                    <option value="light">Minimal social details</option>
                                    <option value="medium">Moderate social texture</option>
                                    <option value="deep">Rich social details and relationships</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1">Mythology/Supernatural</label>
                                <select x-model="config.authorIntent.mythology_layer" 
                                        class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-white">
                                    <option value="absent">No supernatural elements</option>
                                    <option value="subtle">Light supernatural hints</option>
                                    <option value="present">Clear supernatural system</option>
                                </select>
                            </div>
                        </div>

                        <!-- Generate Button -->
                        <button @click="generateStory()" 
                                :disabled="loading"
                                class="w-full mt-6 bg-orange-500 hover:bg-orange-600 disabled:bg-gray-600 text-white font-semibold py-3 rounded-lg transition-colors">
                            <span x-show="!loading">🔥 Generate Story Package</span>
                            <span x-show="loading">⚡ Generating...</span>
                        </button>
                    </div>
                </div>

                <!-- Results Panel -->
                <div class="lg:col-span-2">
                    <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
                        <h2 class="text-xl font-semibold mb-6 text-orange-400">Generated Materials</h2>
                        
                        <!-- Loading State -->
                        <div x-show="loading" class="text-center py-12">
                            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-orange-500"></div>
                            <p class="mt-4 text-gray-400">Forging your story materials...</p>
                        </div>

                        <!-- Error State -->
                        <div x-show="error" class="bg-red-900 border border-red-700 rounded-lg p-4">
                            <p class="text-red-300" x-text="error"></p>
                        </div>

                        <!-- Results -->
                        <div x-show="result && !loading">
                            <!-- Summary -->
                            <div class="bg-gray-700 rounded-lg p-4 mb-6">
                                <h3 class="text-lg font-semibold mb-3">Story Package Summary</h3>
                                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                                    <div class="text-center">
                                        <div class="text-2xl font-bold text-orange-400" x-text="result.summary?.total_materials || 0"></div>
                                        <div class="text-sm text-gray-400">Materials</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-2xl font-bold text-orange-400" x-text="result.summary?.estimated_chapters || 0"></div>
                                        <div class="text-sm text-gray-400">Chapters</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-2xl font-bold text-orange-400" x-text="result.summary?.resistance_events || 0"></div>
                                        <div class="text-sm text-gray-400">Resistance Events</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Story Arc -->
                            <div class="bg-gray-700 rounded-lg p-4 mb-6">
                                <h3 class="text-lg font-semibold mb-3">Story Arc</h3>
                                <div class="space-y-2">
                                    <div><strong>Title:</strong> <span x-text="result.story_arc?.title"></span></div>
                                    <div><strong>Type:</strong> <span x-text="result.story_arc?.arc_type"></span></div>
                                    <div><strong>Estimated Chapters:</strong> <span x-text="result.story_arc?.estimated_chapters"></span></div>
                                    <div><strong>Tension Progression:</strong> <span x-text="(result.story_arc?.tension_progression || []).join(' → ')"></span></div>
                                </div>
                            </div>

                            <!-- Materials -->
                            <div class="bg-gray-700 rounded-lg p-4 mb-6">
                                <h3 class="text-lg font-semibold mb-3">Generated Materials</h3>
                                <div class="space-y-3">
                                    <template x-for="material in result.materials" :key="material.id">
                                        <div class="bg-gray-600 rounded-lg p-3">
                                            <div class="flex justify-between items-start">
                                                <div>
                                                    <span class="font-semibold capitalize" x-text="material.type"></span>
                                                    <span class="text-sm text-gray-400 ml-2" x-text="material.archetype"></span>
                                                </div>
                                                <div class="text-sm text-orange-400">
                                                    Tension: <span x-text="material.tension_level"></span>
                                                </div>
                                            </div>
                                            <div class="mt-2 text-sm text-gray-300" x-html="formatMaterialContent(material.content)"></div>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Pressure Points -->
                            <div x-show="result.pressure_points?.length > 0" class="bg-gray-700 rounded-lg p-4 mb-6">
                                <h3 class="text-lg font-semibold mb-3">⚡ Pressure Points</h3>
                                <div class="space-y-2">
                                    <template x-for="point in result.pressure_points" :key="point.axes + point.element">
                                        <div class="bg-gray-600 rounded p-2">
                                            <span x-text="point.axes"></span> at <span x-text="point.element"></span>
                                            <span class="text-sm text-orange-400 ml-2">(Tension: <span x-text="point.tension"></span>)</span>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Resistance Events -->
                            <div x-show="result.resistance?.unpredictable_events?.event" class="bg-gray-700 rounded-lg p-4 mb-6">
                                <h3 class="text-lg font-semibold mb-3">🎲 Unpredictable Event</h3>
                                <p x-text="result.resistance.unpredictable_events.description"></p>
                            </div>

                            <!-- Emergent Complexity -->
                            <div x-show="result.resistance?.emergent_complexity?.length > 0" class="bg-gray-700 rounded-lg p-4">
                                <h3 class="text-lg font-semibold mb-3">🌪️ Emergent Complexity</h3>
                                <ul class="space-y-1">
                                    <template x-for="complexity in result.resistance.emergent_complexity">
                                        <li class="text-gray-300" x-text="'• ' + complexity"></li>
                                    </template>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- History Section -->
            <div class="mt-8">
                <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
                    <h2 class="text-xl font-semibold mb-6 text-orange-400">Recent Story Packages</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <template x-for="pkg in history" :key="pkg.id">
                            <div class="bg-gray-700 rounded-lg p-4 cursor-pointer hover:bg-gray-600 transition-colors"
                                 @click="loadDetail(pkg.id)">
                                <h3 class="font-semibold" x-text="pkg.title"></h3>
                                <div class="text-sm text-gray-400 mt-1">
                                    <div>Type: <span x-text="pkg.arc_type"></span></div>
                                    <div>Anchor: <span x-text="pkg.structural_anchor"></span></div>
                                    <div>Chapters: <span x-text="pkg.estimated_chapters"></span></div>
                                    <div>Materials: <span x-text="pkg.materials_count"></span></div>
                                    <div>Created: <span x-text="pkg.created_at"></span></div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        function civilizationForge() {
            return {
                // Configuration
                config: {
                    preset: 'stable',
                    structuralAnchor: 'academic_system',
                    authorIntent: {
                        narrative_density: 'medium',
                        power_gradient: 'medium',
                        resource_density: 'medium',
                        perception_complexity: 'medium',
                        conflict_intensity: 'medium',
                        social_thickness: 'medium',
                        mythology_layer: 'present'
                    }
                },
                
                // UI State
                loading: false,
                results: null,
                showResults: false,

                init() {
                    this.loadAnchors();
                    this.loadHistory();
                },

                async loadAnchors() {
                    try {
                        const response = await fetch('/civilization-forge/anchors');
                        const anchors = await response.json();
                        this.updateAnchorInfo();
                    } catch (error) {
                        console.error('Failed to load anchors:', error);
                    }
                },

                updateAnchorInfo() {
                    const anchors = {
                        academic_system: {
                            description: 'Schools, universities, research institutions, and knowledge centers',
                            examples: ['Magic academies', 'Library networks', 'Philosophical schools']
                        },
                        faction_system: {
                            description: 'Political factions, guilds, organizations',
                            examples: ['Game of Thrones houses', 'D&D factions', 'Political parties']
                        },
                        commercial_system: {
                            description: 'Trade, commerce, merchant networks',
                            examples: ['Trading companies', 'Market economies', 'Merchant guilds']
                        }
                    };
                    this.config.anchorInfo = anchors[this.config.structuralAnchor] || {};
                },

                async loadHistory() {
                    try {
                        const response = await fetch('/civilization-forge/history');
                        this.history = await response.json();
                    } catch (error) {
                        console.error('Failed to load history:', error);
                    }
                },

                async generateStory() {
                    Alpine.store('config').loading = true;
                    Alpine.store('config').showResults = false;
                    
                    const form = document.getElementById('storyForm');
                    const formData = new FormData(form);
                    
                    fetch('/civilization-forge/generate', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        Alpine.store('config').results = data;
                        Alpine.store('config').showResults = true;
                        Alpine.store('config').loading = false;
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Alpine.store('config').loading = false;
                        alert('Error generating story package: ' + error.message);
                    });
                    } finally {
                        this.loading = false;
                    }
                },

                async loadDetail(id) {
                    try {
                        const response = await fetch(`/civilization-forge/detail/${id}`);
                        const data = await response.json();
                        
                        // Convert to result format
                        this.result = {
                            story_arc: data.story_arc,
                            world_state: data.world_state,
                            materials: data.materials,
                            pressure_points: [],
                            resistance: { unpredictable_events: { event: null }, emergent_complexity: [] },
                            summary: {
                                total_materials: data.materials.length,
                                estimated_chapters: data.story_arc.estimated_chapters,
                                resistance_events: 0
                            }
                        };
                    } catch (error) {
                        this.error = 'Failed to load story details: ' + error.message;
                    }
                },

                formatMaterialContent(content) {
                    if (typeof content === 'object') {
                        let html = '';
                        for (const [key, value] of Object.entries(content)) {
                            if (typeof value === 'object') {
                                html += `<strong>${key}:</strong><ul>`;
                                for (const [subKey, subValue] of Object.entries(value)) {
                                    html += `<li>${subKey}: ${subValue}</li>`;
                                }
                                html += '</ul>';
                            } else {
                                html += `<strong>${key}:</strong> ${value}<br>`;
                            }
                        }
                        return html;
                    }
                    return content;
                }
            }
        }
    </script>
</body>
</html>
