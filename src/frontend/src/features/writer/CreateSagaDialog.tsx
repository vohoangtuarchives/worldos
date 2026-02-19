"use client";

import { useState } from "react";
import { useUniverses, useCreateSagaFromActive } from "./useWriterApi";
import { Button } from "@/components/ui/button";
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from "@/components/ui/dialog";
import { Label } from "@/components/ui/label";
import { Loader2, Plus, ArrowRight, Activity } from "lucide-react";
import { useRouter } from "next/navigation";
import { cn } from "@/lib/utils";

interface CreateSagaDialogProps {
    children?: React.ReactNode;
}

export function CreateSagaDialog({ children }: CreateSagaDialogProps) {
    const [open, setOpen] = useState(false);
    const [selectedUniverseId, setSelectedUniverseId] = useState<string>("");
    const router = useRouter();

    const { data: universes, isLoading: isLoadingUniverses } = useUniverses();
    const createSaga = useCreateSagaFromActive();

    const handleSubmit = () => {
        createSaga.mutate(
            selectedUniverseId ? { universe_id: selectedUniverseId } : undefined,
            {
                onSuccess: (data) => {
                    setOpen(false);
                    if (data?.id) {
                        router.push(`/writer/sagas/${data.id}`);
                    }
                },
            }
        );
    };

    // Filter universes? Maybe show all but sort by latest.
    // The API returns all universes.
    const availableUniverses = universes || [];

    return (
        <Dialog open={open} onOpenChange={setOpen}>
            <DialogTrigger asChild>
                {children || (
                    <Button size="sm" variant="default" className="gap-2">
                        <Plus className="w-4 h-4" />
                        Create Saga From Active
                    </Button>
                )}
            </DialogTrigger>
            <DialogContent className="sm:max-w-[500px] glass-card border-white/10">
                <DialogHeader>
                    <DialogTitle>Create Saga from Universe</DialogTitle>
                    <DialogDescription>
                        Select an existing Universe to build a Saga around. The Saga will act as the orchestrator for this timeline.
                    </DialogDescription>
                </DialogHeader>

                <div className="flex flex-col gap-4 py-4">
                    <div className="space-y-2">
                        <Label>Select Universe</Label>
                        {isLoadingUniverses ? (
                            <div className="flex items-center gap-2 text-sm text-muted-foreground p-2">
                                <Loader2 className="w-4 h-4 animate-spin" /> Loading universes...
                            </div>
                        ) : availableUniverses.length === 0 ? (
                            <div className="text-sm text-muted-foreground p-2 border rounded-md border-dashed text-center">
                                No universes found. Please run Genesis first.
                            </div>
                        ) : (
                            <div className="grid gap-2 max-h-[300px] overflow-y-auto pr-2 custom-scrollbar">
                                {availableUniverses.map((u) => {
                                    const isSelected = selectedUniverseId === u.id;
                                    // Use status color if available
                                    const statusColor = u.status === 'running' ? 'text-emerald-400' : 'text-muted-foreground';

                                    return (
                                        <div
                                            key={u.id}
                                            onClick={() => setSelectedUniverseId(u.id)}
                                            className={cn(
                                                "flex items-center justify-between p-3 rounded-md border cursor-pointer transition-colors",
                                                isSelected
                                                    ? "border-primary bg-primary/10"
                                                    : "border-border hover:bg-white/5"
                                            )}
                                        >
                                            <div className="flex flex-col gap-1">
                                                <span className="font-medium text-sm">{u.name}</span>
                                                <span className="text-xs text-muted-foreground flex items-center gap-1">
                                                    Created: {u.created_at ? new Date(u.created_at).toLocaleDateString() : 'Unknown'}
                                                </span>
                                            </div>
                                            <Activity className={cn("w-4 h-4", statusColor)} />
                                        </div>
                                    );
                                })}
                            </div>
                        )}
                        {!selectedUniverseId && availableUniverses.length > 0 && (
                            <p className="text-xs text-muted-foreground"> * Select a universe to continue.</p>
                        )}
                    </div>
                </div>

                <DialogFooter>
                    <Button variant="ghost" onClick={() => setOpen(false)}>Cancel</Button>
                    <Button
                        onClick={handleSubmit}
                        disabled={(!selectedUniverseId && availableUniverses.length > 0) || createSaga.isPending || availableUniverses.length === 0}
                        className="gap-2"
                    >
                        {createSaga.isPending && <Loader2 className="w-4 h-4 animate-spin" />}
                        Initialize Saga
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
