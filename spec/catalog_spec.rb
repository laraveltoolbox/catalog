# frozen_string_literal: true

require "tempfile"
require "json"
require "net/http"

RSpec.describe Catalog do
  it "can be represented as_json" do
    expect(described_class.new.as_json).to be_a Hash
  end

  describe "#export" do
    it "exports the JSON catalog to a file" do
      Tempfile.open("json-export") do |f|
        expect { described_class.new.export(path: f.path) }
          .to change { File.read(f.path) }
          .from("").to("#{JSON.pretty_generate(described_class.new.as_json)}\n")
      end
    end
  end

  it "validates against the schema" do
    validation = JSON::Validator.fully_validate(described_class.schema, described_class.new.as_json)
    # Cheating for better error reporting...
    pp validation unless validation.empty?
    expect(validation).to be_empty
  end

  described_class.new.as_json[:category_groups].map { |group| group[:categories] }.flatten.each do |category|
    describe "Category #{category['name'].inspect}" do
      it "does not define duplicates" do
        expect(category["projects"]).to eq category["projects"].uniq
      end
    end
  end

  describe "referenced composer packages" do
    # The full list of package names published on packagist, about 12 MB of JSON
    let(:available_packages) do
      response = Net::HTTP.get_response URI.parse("https://packagist.org/packages/list.json")
      raise "Unexpected packagist response status #{response.code}" unless response.code == "200"

      JSON.parse(response.body).fetch("packageNames").to_set(&:downcase)
    end

    let(:referenced_packages) do
      described_class.new.as_json[:category_groups]
                     .flat_map { |group| group[:categories] }
                     .flat_map { |category| category["projects"] }
                     .map(&:downcase)
                     .sort
                     .uniq
    end

    it "references packages by their vendor-prefixed composer name" do
      expect(referenced_packages.reject { |package| package.count("/") == 1 }).to eq []
    end

    it "references only packages that actually exist on packagist" do
      expect(referenced_packages.reject { |package| available_packages.include?(package) }).to eq []
    end
  end
end
