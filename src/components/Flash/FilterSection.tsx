type Props = {
  title: string;
  options: string[];
  selected: string[];
  onChange: (newValues: string[]) => void;
};

const FilterSection: React.FC<Props> = ({
  title,
  options,
  selected,
  onChange,
}) => {
  const toggleOption = (value: string) => {
    if (selected.includes(value)) {
      onChange(selected.filter((v) => v !== value));
    } else {
      onChange([...selected, value]);
    }
  };

  return (
    <div className="mb-6">
      <h4 className="text-white font-semibold mb-2">{title}</h4>
      <div className="flex-col-gauche">
        {options.map((opt) => (
          <label
            key={opt}
            className="flex items-center gap-2 text-white text-sm"
          >
            <input
              type="checkbox"
              value={opt}
              checked={selected.includes(opt)}
              onChange={() => toggleOption(opt)}
              className="caseCocher"
            />
            {opt}
          </label>
        ))}
      </div>
    </div>
  );
};

export default FilterSection;
