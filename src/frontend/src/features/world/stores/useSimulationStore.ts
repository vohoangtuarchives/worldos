import { create } from 'zustand';

export interface ChronicleEvent {
    id?: string;
    universe_id?: string;
    tick: number;
    type: string;
    title: string;
    severity: string;
    payload?: any;
}

interface SimulationState {
    // Numeric targets for LERP (Physics)
    targetVector: number[]; // Removed later when fully migrated, keeping for legacy components
    currentVector: number[];

    targetStability: number;
    currentStability: number;

    targetEntropy: number;
    currentEntropy: number;

    // V6 Ontology Vectors
    targetIdeology: Record<string, number>;
    currentIdeology: Record<string, number>;

    targetCulture: Record<string, number>;
    currentCulture: Record<string, number>;

    lifecycle: string;
    influenceMass: number;

    year: number;
    phase: string;

    chronicleEvents: ChronicleEvent[];

    // Actions
    setTargetState: (data: any) => void;
    addChronicleEvent: (event: ChronicleEvent) => void;
    updateInterpolation: (factor: number) => void;
}

export const useSimulationStore = create<SimulationState>((set) => ({
    targetVector: new Array(17).fill(0),
    currentVector: new Array(17).fill(0),

    targetStability: 0.5,
    currentStability: 0.5,

    targetEntropy: 0.1,
    currentEntropy: 0.1,

    targetIdeology: { militarism: 0, spiritualism: 0, expansionism: 0, collectivism: 0, purity: 0 },
    currentIdeology: { militarism: 0, spiritualism: 0, expansionism: 0, collectivism: 0, purity: 0 },

    targetCulture: { aesthetic_density: 0, intellectual_curiosity: 0, tradition_rigidity: 0, individual_expression: 0 },
    currentCulture: { aesthetic_density: 0, intellectual_curiosity: 0, tradition_rigidity: 0, individual_expression: 0 },

    lifecycle: 'dormant',
    influenceMass: 0,

    year: 0,
    phase: 'PRIMORDIAL',

    chronicleEvents: [],

    setTargetState: (data) => set((state) => {
        // Hỗ trợ cả payload SSE cũ (v, s, c.e, y, p) và HTTP REST mới (state_vector, ideology_vector, culture_vector)
        const newIdeology = data.ideology_vector || state.targetIdeology;
        const newCulture = data.culture_vector || state.targetCulture;
        const newState = data.state_vector || {};

        return {
            targetVector: data.v || state.targetVector,
            targetStability: data.s ?? (data.stability_index ?? state.targetStability),
            targetEntropy: data.c?.e ?? (newState.entropy ?? state.targetEntropy),
            year: data.y ?? (data.tick ?? state.year),
            phase: data.p ?? (data.lifecycle_state ?? state.phase),

            targetIdeology: newIdeology,
            targetCulture: newCulture,
            lifecycle: data.lifecycle_state || state.lifecycle,
            influenceMass: data.influence_mass ?? state.influenceMass,
        };
    }),

    addChronicleEvent: (event) => set((state) => ({
        chronicleEvents: [event, ...state.chronicleEvents].slice(0, 50)
    })),

    updateInterpolation: (factor: number) => set((state) => {
        // Linear interpolation: current = current + (target - current) * factor
        const lerp = (start: number, end: number, f: number) => start + (end - start) * f;

        const lerpObject = (current: Record<string, number>, target: Record<string, number>) => {
            const result: Record<string, number> = { ...current };
            for (const key in target) {
                if (typeof target[key] === 'number') {
                    result[key] = lerp(current[key] || 0, target[key], factor);
                }
            }
            return result;
        };

        return {
            currentVector: state.currentVector.map((val, i) =>
                lerp(val, state.targetVector[i] || 0, factor)
            ),
            currentStability: lerp(state.currentStability, state.targetStability, factor),
            currentEntropy: lerp(state.currentEntropy, state.targetEntropy, factor),
            currentIdeology: lerpObject(state.currentIdeology, state.targetIdeology),
            currentCulture: lerpObject(state.currentCulture, state.targetCulture),
        };
    }),
}));
