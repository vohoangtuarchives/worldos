import { create } from 'zustand';

export interface ChronicleEvent {
    world_id: string;
    year: number;
    type: string;
    title: string;
    description: string;
    severity: 'LOW' | 'MEDIUM' | 'HIGH' | 'CRITICAL';
    metadata: any;
}

interface SimulationState {
    // Numeric targets for LERP
    targetVector: number[];
    currentVector: number[];

    targetStability: number;
    currentStability: number;

    targetEntropy: number;
    currentEntropy: number;

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

    year: 0,
    phase: 'PRIMORDIAL',

    chronicleEvents: [],

    setTargetState: (data) => set((state) => ({
        targetVector: data.v || state.targetVector,
        targetStability: data.s ?? state.targetStability,
        targetEntropy: data.c?.e ?? state.targetEntropy,
        year: data.y ?? state.year,
        phase: data.p ?? state.phase,
    })),

    addChronicleEvent: (event) => set((state) => ({
        chronicleEvents: [event, ...state.chronicleEvents].slice(0, 50)
    })),

    updateInterpolation: (factor: number) => set((state) => {
        // Linear interpolation: current = current + (target - current) * factor
        const lerp = (start: number, end: number, f: number) => start + (end - start) * f;

        return {
            currentVector: state.currentVector.map((val, i) =>
                lerp(val, state.targetVector[i], factor)
            ),
            currentStability: lerp(state.currentStability, state.targetStability, factor),
            currentEntropy: lerp(state.currentEntropy, state.targetEntropy, factor),
        };
    }),
}));
