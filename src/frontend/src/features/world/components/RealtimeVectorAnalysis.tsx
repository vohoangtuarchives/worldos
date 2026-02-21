"use client";

import React, { useRef, useEffect } from 'react';
import { useSimulationStore } from '../stores/useSimulationStore';

const KEYS = [
    'ce', 'sc', 'tech', 'stab', 'pros', 'mp', 'ie',
    'legit', 'ec', 'ineq', 'sust', 'myst', 'legacy',
    'exp', 'info', 'mob', 'curv'
];

export function RealtimeVectorAnalysis() {
    const canvasRef = useRef<HTMLCanvasElement>(null);
    const currentVector = useSimulationStore((state) => state.currentVector);

    useEffect(() => {
        const canvas = canvasRef.current;
        if (!canvas) return;

        const ctx = canvas.getContext('2d');
        if (!ctx) return;

        const draw = () => {
            const { width, height } = canvas;
            ctx.clearRect(0, 0, width, height);

            const barWidth = width / KEYS.length;
            const centerY = height / 2;
            const maxBarHeight = height / 2.5;

            KEYS.forEach((key, i) => {
                const val = currentVector[i] || 0;
                const x = i * barWidth;
                const barHeight = val * maxBarHeight;

                // Draw bar
                ctx.fillStyle = val >= 0 ? 'rgba(56, 189, 248, 0.8)' : 'rgba(244, 63, 94, 0.8)';
                ctx.fillRect(x + 2, centerY, barWidth - 4, -barHeight);

                // Draw baseline
                ctx.strokeStyle = 'rgba(255, 255, 255, 0.1)';
                ctx.beginPath();
                ctx.moveTo(x, centerY);
                ctx.lineTo(x + barWidth, centerY);
                ctx.stroke();

                // Draw label
                ctx.fillStyle = 'rgba(255, 255, 255, 0.5)';
                ctx.font = '8px Inter, system-ui';
                ctx.fillText(key, x + 4, centerY + 15);
            });

            // Request next frame is handled by the useWorldStream Raf loop
            // But we can also add a local draw loop if we want independent frame rate
        };

        // Since currentVector changes 60 times a second due to store interpolation,
        // we can just react to it.
        draw();
    }, [currentVector]);

    return (
        <div className="w-full h-64 bg-slate-900/50 rounded-xl overflow-hidden border border-slate-800 relative">
            <div className="absolute top-2 left-3 flex items-center gap-2">
                <div className="h-2 w-2 rounded-full bg-sky-500 animate-pulse" />
                <span className="text-[10px] font-bold text-sky-400 uppercase tracking-widest">Hyper-Stream Active (60fps)</span>
            </div>
            <canvas
                ref={canvasRef}
                width={800}
                height={300}
                className="w-full h-full p-4"
            />
        </div>
    );
}
