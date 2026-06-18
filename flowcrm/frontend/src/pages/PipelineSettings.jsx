import { Link } from 'react-router-dom';
import { useEffect, useState } from 'react';
import { createStage, listPipelines, listStages, removeStage, updateStage } from '../api/pipelines';
import useProfessionMode from '../hooks/useProfessionMode';
import PageHeader from '../components/shared/PageHeader';
import Button from '../components/ui/Button';
import Card from '../components/ui/Card';
import Input from '../components/ui/Input';

export default function PipelineSettings() {
  const { config } = useProfessionMode();
  const [stages, setStages] = useState([]);
  const [pipelines, setPipelines] = useState([]);
  const [name, setName] = useState('');

  const load = () => Promise.all([listStages().then(setStages), listPipelines().then(setPipelines)]);
  useEffect(() => { load(); }, []);

  const addStage = async (e) => {
    e.preventDefault();
    const pipelineId = pipelines[0]?.id;
    await createStage({ name, pipeline_id: pipelineId, color: '#4F8CFF' });
    setName('');
    load();
  };

  return (
    <>
      <PageHeader
        title="Configurar etapas"
        subtitle={config.pipelineSubtitle}
        action={<Link to="/pipeline"><Button variant="secondary">Voltar ao Kanban</Button></Link>}
      />
      <Card className="mb-4">
        <form className="flex flex-wrap gap-3" onSubmit={addStage}>
          <Input label="Nova etapa" value={name} onChange={(e) => setName(e.target.value)} required />
          <Button type="submit" className="self-end">Adicionar etapa</Button>
        </form>
      </Card>
      <div className="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
        {stages.map((stage) => (
          <Card key={stage.id}>
            <div className="flex items-center justify-between gap-3">
              <div className="flex items-center gap-3">
                <span className="h-3 w-3 rounded-full" style={{ background: stage.color || '#4F8CFF' }} />
                <strong>{stage.name}</strong>
              </div>
              <div className="flex gap-2 text-xs text-slate-400">
                {stage.is_won && <span>Ganho</span>}
                {stage.is_lost && <span>Perdido</span>}
              </div>
            </div>
            <div className="mt-3 flex gap-2">
              <Button size="sm" variant="ghost" onClick={() => updateStage(stage.id, { is_won: !stage.is_won }).then(load)}>Ganho</Button>
              <Button size="sm" variant="ghost" onClick={() => updateStage(stage.id, { is_lost: !stage.is_lost }).then(load)}>Perdido</Button>
              <Button size="sm" variant="ghost" onClick={() => removeStage(stage.id).then(load)}>Excluir</Button>
            </div>
          </Card>
        ))}
      </div>
    </>
  );
}
