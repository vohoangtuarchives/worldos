"use client";

import { useState } from "react";
import { useAuth } from "@/lib/auth/AuthProvider";
import { useRouter } from "next/navigation";
import { ShieldAlert, Terminal, Lock, KeyRound, Cpu } from "lucide-react";

export default function LoginPage() {
    const [email, setEmail] = useState("");
    const [password, setPassword] = useState("");
    const [error, setError] = useState("");
    const [isLoading, setIsLoading] = useState(false);

    const { login } = useAuth();
    const router = useRouter();

    const handleLogin = async (e: React.FormEvent) => {
        e.preventDefault();
        setError("");
        setIsLoading(true);

        try {
            await login({ email, password });
            router.push("/console"); // Theo route mới
        } catch (err: any) {
            if (err.response?.status === 401 || err.response?.status === 422) {
                setError("ACCESS DENIED: Invalid Operator Credentials.");
            } else {
                setError("SYSTEM ERROR: Nexus uplink failed. Check connection.");
            }
        } finally {
            setIsLoading(false);
        }
    };

    return (
        <div className="min-h-screen bg-cosmic-grid flex items-center justify-center p-4 relative overflow-hidden">
            {/* Background Decorators */}
            <div className="absolute top-[-20%] left-[-10%] w-[50%] h-[50%] bg-primary/10 blur-[150px] rounded-full pointer-events-none mix-blend-screen" />
            <div className="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-destructive/10 blur-[150px] rounded-full pointer-events-none mix-blend-screen" />

            {/* Main Terminal Box */}
            <div className="w-full max-w-md relative z-10 group">
                <div className="absolute -inset-0.5 bg-gradient-to-r from-primary/50 to-accent/50 rounded-xl blur opacity-30 group-hover:opacity-60 transition duration-1000 group-hover:duration-200"></div>
                <div className="glass-panel relative rounded-xl overflow-hidden backdrop-blur-3xl shadow-[0_0_40px_rgba(0,0,0,0.8)] border border-white/10">

                    {/* Header */}
                    <div className="border-b border-white/10 bg-black/40 p-6 flex flex-col items-center gap-4">
                        <div className="relative">
                            <div className="absolute -inset-4 bg-primary/20 blur-xl rounded-full" />
                            <Cpu className="w-12 h-12 text-primary relative z-10 drop-shadow-[0_0_15px_theme(colors.primary.DEFAULT)] animate-pulse" />
                        </div>
                        <div className="text-center space-y-1">
                            <h1 className="font-mono text-xl md:text-2xl font-bold tracking-widest text-transparent bg-clip-text bg-gradient-to-r from-white to-white/60 uppercase">
                                SECURITY CLEARANCE
                            </h1>
                            <p className="font-mono text-xs text-primary/80 tracking-widest uppercase">
                                WorldOS v4 // God Console Access
                            </p>
                        </div>
                    </div>

                    {/* Form Content */}
                    <div className="p-8">
                        <form onSubmit={handleLogin} className="space-y-6">

                            {/* Error Box */}
                            {error && (
                                <div className="bg-destructive/10 border border-destructive/50 rounded-lg p-4 flex items-start gap-3 animate-in fade-in slide-in-from-top-2">
                                    <ShieldAlert className="w-5 h-5 text-destructive shrink-0 mt-0.5 shadow-glow" />
                                    <p className="text-sm font-mono text-destructive tracking-wide leading-relaxed">
                                        {error}
                                    </p>
                                </div>
                            )}

                            {/* Input Fields */}
                            <div className="space-y-4 font-mono">
                                <div className="space-y-2">
                                    <label className="text-xs text-muted-foreground uppercase tracking-wider flex items-center gap-2">
                                        <Terminal className="w-3 h-3 text-primary" />
                                        Operator ID (Email)
                                    </label>
                                    <div className="relative">
                                        <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <Lock className="w-4 h-4 text-white/30" />
                                        </div>
                                        <input
                                            type="email"
                                            value={email}
                                            onChange={(e) => setEmail(e.target.value)}
                                            required
                                            placeholder="admin@worldos.com"
                                            className="w-full bg-black/40 border border-white/10 rounded-lg pl-10 pr-4 py-3 text-sm text-foreground placeholder:text-white/20 focus:outline-none focus:border-primary/50 focus:ring-1 focus:ring-primary/50 transition-all font-sans"
                                        />
                                    </div>
                                </div>

                                <div className="space-y-2">
                                    <label className="text-xs text-muted-foreground uppercase tracking-wider flex items-center gap-2">
                                        <Terminal className="w-3 h-3 text-accent" />
                                        Authorization Code
                                    </label>
                                    <div className="relative">
                                        <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <KeyRound className="w-4 h-4 text-white/30" />
                                        </div>
                                        <input
                                            type="password"
                                            value={password}
                                            onChange={(e) => setPassword(e.target.value)}
                                            required
                                            placeholder="••••••••"
                                            className="w-full bg-black/40 border border-white/10 rounded-lg pl-10 pr-4 py-3 text-sm text-foreground placeholder:text-white/20 focus:outline-none focus:border-accent/50 focus:ring-1 focus:ring-accent/50 transition-all font-sans"
                                        />
                                    </div>
                                </div>
                            </div>

                            {/* Action */}
                            <button
                                type="submit"
                                disabled={isLoading}
                                className={`w-full relative group overflow-hidden rounded-lg font-mono font-bold tracking-widest uppercase transition-all duration-300 ${isLoading
                                        ? "bg-white/5 border border-white/10 text-white/50 cursor-not-allowed py-4"
                                        : "bg-primary/10 border border-primary/40 text-primary hover:bg-primary/20 hover:text-white hover:shadow-[0_0_20px_theme(colors.primary.DEFAULT/0.4)] py-4"
                                    }`}
                            >
                                {!isLoading && (
                                    <div className="absolute inset-0 w-1/4 h-full bg-gradient-to-r from-transparent via-white/20 to-transparent -skew-x-12 -translate-x-full group-hover:animate-shimmer" />
                                )}
                                {isLoading ? (
                                    <span className="flex items-center justify-center gap-3">
                                        <div className="w-4 h-4 rounded-full border-2 border-primary border-t-transparent animate-spin" />
                                        AUTHENTICATING...
                                    </span>
                                ) : (
                                    "Initiate Uplink"
                                )}
                            </button>
                        </form>
                    </div>

                    {/* Footer Decor */}
                    <div className="bg-black/80 px-4 py-3 border-t border-white/5 flex justify-between items-center text-[10px] font-mono text-white/30 uppercase">
                        <span>SECURE PROTOCOL V4</span>
                        <span className="flex items-center gap-1.5">
                            <span className="w-1.5 h-1.5 rounded-full bg-success animate-pulse shadow-[0_0_5px_theme(colors.success.DEFAULT)]"></span>
                            NODE ACTIVE
                        </span>
                    </div>

                </div>
            </div>
        </div>
    );
}
