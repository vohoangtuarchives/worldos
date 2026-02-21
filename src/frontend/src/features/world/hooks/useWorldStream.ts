import { useEffect } from 'react';
import { decode } from '@msgpack/msgpack';
import { useSimulationStore } from '../stores/useSimulationStore';

export function useWorldStream(worldId: string | null) {
    const setTargetState = useSimulationStore((state) => state.setTargetState);
    const updateInterpolation = useSimulationStore((state) => state.updateInterpolation);

    useEffect(() => {
        if (!worldId) return;

        // SSE connection to the hyper-performance stream
        // Using a relative path which should be proxied or direct depending on setup
        const eventSource = new EventSource(`/api/realtime/stream/${worldId}`);

        const handleMetric = (event: MessageEvent) => {
            try {
                // SSE data is base64 encoded MessagePack
                const binaryString = atob(event.data);
                const bytes = new Uint8Array(binaryString.length);
                for (let i = 0; i < binaryString.length; i++) {
                    bytes[i] = binaryString.charCodeAt(i);
                }

                const decoded = decode(bytes);
                useSimulationStore.getState().setTargetState(decoded);
            } catch (error) {
                console.error('Failed to decode metric data:', error);
            }
        };

        const handleChronicle = (event: MessageEvent) => {
            try {
                const binaryString = atob(event.data);
                const bytes = new Uint8Array(binaryString.length);
                for (let i = 0; i < binaryString.length; i++) {
                    bytes[i] = binaryString.charCodeAt(i);
                }

                const decoded = decode(bytes);
                useSimulationStore.getState().addChronicleEvent(decoded as any);
            } catch (error) {
                console.error('Failed to decode chronicle data:', error);
            }
        };

        eventSource.addEventListener('metric', handleMetric);
        eventSource.addEventListener('chronicle', handleChronicle);

        // Interpolation Loop (60fps)
        let rafId: number;
        const animate = () => {
            // Sensitivity factor: how fast we move towards target
            // 0.1 means 10% of the distance covered per frame (~6.2ms if 60fps)
            // This creates a very smooth "weighted average" drift
            updateInterpolation(0.12);
            rafId = requestAnimationFrame(animate);
        };
        rafId = requestAnimationFrame(animate);

        return () => {
            eventSource.removeEventListener('metric', handleMetric);
            eventSource.removeEventListener('chronicle', handleChronicle);
            eventSource.close();
            cancelAnimationFrame(rafId);
        };
    }, [worldId, setTargetState, updateInterpolation]);
}
