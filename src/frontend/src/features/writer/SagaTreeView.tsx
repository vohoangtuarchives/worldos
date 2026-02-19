"use client";

import { useSagaTree } from "./useWriterApi";
import { GitBranch, Orbit, Activity, ShieldAlert, Archive } from "lucide-react";
import { cn } from "@/lib/utils";
import { Badge } from "@/components/ui/badge";

export function SagaTreeView({ sagaId }: { sagaId: string }) {
  const { data, isLoading, error } = useSagaTree(sagaId);

  if (isLoading) return <div className="p-4 animate-pulse text-muted-foreground italic">Analyzing Timeline Threads...</div>;
  if (error) return <div className="p-4 text-error font-bold border border-error/20 rounded-lg">Scan Failed: Saga Inaccessible</div>;

  const nodes = data?.nodes ?? [];
  if (!nodes.length) return <div className="p-8 text-center text-muted-foreground border border-dashed rounded-xl">Chronicle Vacant: No events recorded.</div>;

  // Build tree structure
  const nodeMap = new Map();
  nodes.forEach((n: any) => nodeMap.set(n.id, { ...n, children: [] }));

  const rootNodes: any[] = [];
  nodes.forEach((n: any) => {
    if (n.parentId && nodeMap.has(n.parentId)) {
      nodeMap.get(n.parentId).children.push(nodeMap.get(n.id));
    } else {
      rootNodes.push(nodeMap.get(n.id));
    }
  });

  const renderNode = (node: any, depth = 0) => (
    <div key={node.id} className="space-y-4">
      <div className={cn(
        "glass-card p-3 relative hover:border-primary/40 transition-all",
        depth > 0 && "ml-8 before:absolute before:-left-8 before:top-1/2 before:w-8 before:h-px before:bg-primary/20"
      )}>
        <div className="flex items-center justify-between">
          <div className="flex items-center gap-3">
            <div className={cn(
              "h-8 w-8 rounded-lg flex items-center justify-center",
              node.status === 'ACTIVE' ? "bg-primary/10 text-primary" : "bg-muted text-muted-foreground"
            )}>
              {depth === 0 ? <Orbit className="h-4 w-4" /> : <GitBranch className="h-4 w-4" />}
            </div>
            <div>
              <p className="text-sm font-bold">{node.name}</p>
              <div className="flex items-center gap-2 font-mono text-[9px] uppercase tracking-tighter text-muted-foreground">
                <span>Era: {node.current_era}</span>
                {node.age != null && <span>• Age: {node.age}</span>}
              </div>
            </div>
          </div>

          <div className="flex items-center gap-2">
            {node.status === 'COLLAPSED' && <Badge variant="destructive" className="h-5 text-[9px]">COLLAPSED</Badge>}
            {node.universe_status === 'archived' && <Badge variant="secondary" className="h-5 text-[9px]">ARCHIVED</Badge>}
            <Badge variant="outline" className="h-5 text-[9px] font-mono">{node.status}</Badge>
          </div>
        </div>
      </div>

      {node.children.length > 0 && (
        <div className="relative pl-4 space-y-4 py-2 border-l border-primary/10 ml-4">
          {node.children.map((child: any) => renderNode(child, depth + 1))}
        </div>
      )}
    </div>
  );

  return (
    <div className="space-y-8 p-1">
      <div className="flex items-center justify-between mb-4">
        <h3 className="text-xs font-bold uppercase tracking-widest text-muted-foreground">Timeline Branching Structure</h3>
        <Badge variant="secondary" className="text-[10px]">{nodes.length} SEQUENCES</Badge>
      </div>
      <div className="space-y-6">
        {rootNodes.map(root => renderNode(root))}
      </div>
    </div>
  );
}
