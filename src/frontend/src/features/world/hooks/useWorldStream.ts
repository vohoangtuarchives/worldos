import { useEffect } from 'react';
import { useSimulationStore } from '../stores/useSimulationStore';

export function useWorldStream(universeId: string | null) {
    const setTargetState = useSimulationStore((state) => state.setTargetState);
    const updateInterpolation = useSimulationStore((state) => state.updateInterpolation);
    const addChronicleEvent = useSimulationStore((state) => state.addChronicleEvent);

    useEffect(() => {
        if (!universeId) return;

        let isPolling = true;

        const pollData = async () => {
            if (!isPolling) return;
            try {
                const res = await fetch(`/api/v6/evolution/universes/${universeId}`);
                if (res.ok) {
                    const data = await res.json();
                    setTargetState(data);
                }

                // Poll chronicles
                const resChrons = await fetch(`/api/v6/evolution/universes/${universeId}/chronicles`);
                if (resChrons.ok) {
                    const chrons = await resChrons.json();
                    // Just take the latest ones and add them if they are new, but for simplicity we assume the store only keeps unique or we just add them
                    // Since the API returns top 100 desc, we might need a smart way to deduplicate in store. 
                    // For now, let's just let the store handle it or we can pass the whole array
                    if (chrons && chrons.length > 0) {
                        useSimulationStore.setState({ chronicleEvents: chrons });
                    }
                }
            } catch (err) {
                console.error("Failed to poll universe data:", err);
            }
        };

        // Initial poll
        pollData();

        // Fallback polling interval (since SSE is gone)
        const intervalId = setInterval(pollData, 5000);

        // Interpolation Loop (60fps)
        let rafId: number;
        const animate = () => {
            updateInterpolation(0.12);
            rafId = requestAnimationFrame(animate);
        };
        rafId = requestAnimationFrame(animate);

        return () => {
            isPolling = false;
            clearInterval(intervalId);
            cancelAnimationFrame(rafId);
        };
    }, [universeId, setTargetState, updateInterpolation]);
}
